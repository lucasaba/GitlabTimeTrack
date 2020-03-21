<?php

namespace App\Controller;

use App\Entity\Issue;
use App\Entity\Project;
use App\Form\Type\ChooseProjectsType;
use App\Service\GitlabRequestService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Exception;
use GuzzleHttp\Exception\ClientException;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var GitlabRequestService
     */
    protected $gitlabRequestService;

    /**
     * @var Pdf
     */
    protected $pdf;

    public function __construct(
        EntityManagerInterface $entityManager,
        GitlabRequestService $gitlabRequestService,
        Pdf $pdf
    ) {
        $this->entityManager = $entityManager;
        $this->gitlabRequestService = $gitlabRequestService;
        $this->pdf = $pdf;
    }

    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        $numberOfProjects = $this->entityManager->getRepository(Project::class)->countProjects();
        $numberOfIssues = $this->entityManager->getRepository(Issue::class)->countIssues();
        $totalTimeSpent = $this->entityManager->getRepository(Issue::class)->countTimeSpent();
        $totalEstimatedTime = $this->entityManager->getRepository(Issue::class)->countEstimatedTime();

        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'numberOfProjects' => $numberOfProjects,
            'numberOfIssues' => $numberOfIssues,
            'totalTimeSpent' => $totalTimeSpent,
            'totalEstimatedTime' => $totalEstimatedTime
        ]);
    }

    /**
     * Show all available projects on GitLab and let the user choose those
     * to be tracked
     *
     * @Route("/update-projects", name="update_projects")
     *
     * @param Request $request
     * @return Response
     */
    public function chooseProjectsAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository(Project::class);

        /**
         * We need to fetch all projects from our GitLab server
         */
        try {
            $gitlabProjects = $this->gitlabRequestService->getProjects();
        } catch (InvalidArgumentException | ClientException $e) {
            $this->addFlash('warning', 'An exception has been thrown: ' . $e->getMessage());
            return $this->redirectToRoute('home');
        }

        $projects = $this->orderProjects($gitlabProjects, $repository);
        $form = $this->createForm(ChooseProjectsType::class, null, array(
            'gitlabProjects' => $projects
        ));

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $this->handleProjectSelectionForm($projects, $form);
            return $this->redirectToRoute('home');
        }

        return $this->render('default/update_projects.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/clear-projects-cache", name="clear_projects_cache")
     *
     * @return Response
     * @throws InvalidArgumentException
     */
    public function clearProjectsCacheAction()
    {
        $this->gitlabRequestService->clearProjectsCache();

        return $this->redirectToRoute('update_projects');
    }

    /**
     * @Route("/project/{id}/view", name="view_project")
     *
     * @param Project $project
     * @return Response
     */
    public function viewProjectAction(Project $project)
    {
        $issues = $this->entityManager->getRepository(Issue::class)->findTimedIssuesRelatedToProject($project);

        return $this->render('default/view_project_issues.html.twig', [
            'issues' => $issues,
            'project' => $project
        ]);
    }

    /**
     * @Route("/project/{id}/update-issues", name="update_projects_issues")
     *
     * @param Project $project
     * @return Response
     * @throws Exception
     */
    public function updateProjectIssues(Project $project)
    {
        /**
         * We need to fetch all project's issues from our GitLab server
         */
        $gitlabProjectIssues = $this->gitlabRequestService->getProjectsIssues($project);

        $updated = 0;
        $inserted = 0;

        foreach ($gitlabProjectIssues as $issue) {
            $newIssue = $this->entityManager->getRepository(Issue::class)
                ->findOneBy(['gitlabId' => $issue->id]);

            if ($newIssue == null) {
                // We have to insert a new issue
                $newIssue = new Issue();
                $newIssue->setTitle($issue->title)
                    ->setGitlabId($issue->id)
                    ->setIssueNumber($issue->iid)
                    ->setProject($project)
                    ->setCreatedAt($this->gitlabTimeToW3CTime($issue->created_at))
                    ->setUpdatedAt($this->gitlabTimeToW3CTime($issue->updated_at))
                    ->setStatus($issue->state)
                    ->setTimeEstimate($issue->time_stats->time_estimate)
                    ->setTotalTimeSpent($issue->time_stats->total_time_spent);
                $this->entityManager->persist($newIssue);
                $inserted++;
            } else {
                /**
                 * We have to test if the issue has been updated
                 */
                $lastUpdated = $this->gitlabTimeToW3CTime($issue->updated_at);
                /**
                 * @var $newIssue Issue
                 */
                if ($lastUpdated->getTimestamp() > $newIssue->getUpdatedAt()->getTimestamp()) {
                    $newIssue->setStatus($issue->state)
                        ->setUpdatedAt(new DateTime($issue->updated_at))
                        ->setTimeEstimate($issue->time_stats->time_estimate)
                        ->setTotalTimeSpent($issue->time_stats->total_time_spent);
                    $this->entityManager->persist($newIssue);
                    $updated++;
                }
            }
        }

        $this->entityManager->flush();

        if($updated > 0) {
            $this->addFlash('success', "$updated issues have been updated.");
        }

        if($inserted > 0) {
            $this->addFlash('success', "$inserted issues have been added.");
        }

        return $this->redirectToRoute('view_project', ['id' => $project->getId()]);
    }

    /**
     * @Route("/project/{id}/export-to-pdf", name="export_project_to_pdf")
     *
     * @param Project $project
     * @return Response
     * @throws Exception
     */
    public function exportProjectToPdfAction(Project $project)
    {
        $issues = $this->getDoctrine()->getRepository(Issue::class)->findTimedIssuesRelatedToProject($project);

        $html = $this->renderView('default/pdf_project_issues.html.twig', array(
            'issues'  => $issues,
            'projetc' => $project
        ));

        return new PdfResponse(
            $this->pdf->getOutputFromHtml($html),
            'issues.pdf'
        );
    }

    /**
     * We need to simplify the datetime handling because
     * gitlab is much more precise then MySql
     *
     *
     * @param $gitlabTime string
     * @return DateTime
     * @throws Exception
     */
    private function gitlabTimeToW3CTime($gitlabTime)
    {
        /**
         * Gitlab uses RFC3339_EXTENDED date format
         * 'Y-m-d\TH:i:s.vP'
         * We have to transform it into a much simplier ('Y-m-d\TH:i:s') format
         */
        $date = new DateTime($gitlabTime);
        return new DateTime($date->format('Y-m-d\TH:i:s'));
    }

    /**
     * @param array $gitlabProjects
     * @param ObjectRepository $repository
     * @return array
     */
    private function orderProjects(array $gitlabProjects, ObjectRepository $repository): array
    {
        $projects = [];
        foreach ($gitlabProjects as $project) {
            $projects[] = [
                'gitlabId' => $project->id,
                'name' => $project->name,
                'avatarUrl' => $project->avatar_url,
                'associated' => $repository->findOneBy([
                    'gitlabId' => $project->id] //We check if the project is already in our database
                )
            ];
        }

        /**
         * Let's reorder by name the list of projects
         */
        usort($projects, function ($val1, $val2) {
            if ($val1['name'] > $val2['name']) {
                return 1;
            }
            return -1;
        });
        return $projects;
    }

    /**
     * @param array $projects
     * @param FormInterface $form
     */
    private function handleProjectSelectionForm(array $projects, FormInterface $form): void
    {
        /**
         * Handling form submission
         */
        foreach ($projects as $gitlabProject) {
            $value = $form->get('project_' . $gitlabProject['gitlabId'])->getData();
            if ($value === false && $gitlabProject['associated'] != null) {
                /**
                 * The value was unchecked but the project is in the DB
                 * Remove the project
                 */
                $this->entityManager->remove($gitlabProject['associated']);
            } elseif ($value === true && $gitlabProject['associated'] == null) {
                /**
                 * The value was checked and the project is not in the DB
                 * Persist the new Project
                 */
                $project = new Project();
                $project->setName($gitlabProject['name'])
                    ->setGitlabId($gitlabProject['gitlabId'])
                    ->setAvatarUrl($gitlabProject['avatarUrl']);
                $this->entityManager->persist($project);
            }
        }
        $this->entityManager->flush();
    }
}
