<?php

namespace App\Controller;

use App\Entity\Issue;
use App\Entity\Milestone;
use App\Entity\Note;
use App\Entity\Project;
use App\Form\Type\ChooseProjectsType;
use App\Service\Chart\Chart;
use App\Service\GitlabRequestService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use GuzzleHttp\Exception\ClientException;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProjectController extends AbstractController
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var GitlabRequestService */
    private $gitlabRequestService;

    /** @var Pdf */
    private $pdf;

    private $chart;

    private $issueInserted;
    private $issueUpdated;
    private $milestoneInserted;
    private $milestoneUpdated;

    public function __construct(
        EntityManagerInterface $entityManager,
        GitlabRequestService $gitlabRequestService,
        Chart $chart,
        Pdf $pdf
    ) {
        $this->entityManager = $entityManager;
        $this->gitlabRequestService = $gitlabRequestService;
        $this->chart = $chart;
        $this->pdf = $pdf;
    }

    /**
     * @Route("/", name="home")
     */
    public function index(): Response
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
     * @Route("/update-projects", name="update_projects")
     */
    public function chooseProjectsAction(Request $request): Response
    {
        $repository = $this->getDoctrine()->getRepository(Project::class);

        try {
            $gitlabProjects = $this->gitlabRequestService->getProjects($request->get('visibility'));
        } catch (InvalidArgumentException | ClientException $e) {
            $this->addFlash('warning', 'An exception has been thrown: ' . $e->getMessage());
            return $this->redirectToRoute('home');
        }

        $projects = $this->orderProjectsByName($gitlabProjects, $repository);
        $form = $this->createForm(ChooseProjectsType::class, null, array(
            'gitlabProjects' => $projects
        ));

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleProjectSelectionForm($projects, $form);
            return $this->redirectToRoute('home');
        }

        return $this->render('default/update_projects.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/clear-projects-cache", name="clear_projects_cache")
     */
    public function clearProjectsCacheAction(Request $request): Response
    {
        $this->gitlabRequestService->clearProjectsCache();

        return $this->redirectToRoute('update_projects', ['visibility' => $request->get('visibility')]);
    }

    /**
     * @Route("/project/{id}/view", name="view_project")
     */
    public function viewProjectAction(Request $request, Project $project): Response
    {
        $issues = $this->entityManager->getRepository(Issue::class)->findIssuesRelatedToProject($project);

        return $this->render('default/view_project_issues.html.twig', [
            'issues' => $issues,
            'project' => $project,
            'spendByDate' => $this->chart->timeSpendByDate(...$issues)
        ]);
    }

    /**
     * @Route("/project/{id}/update-issues", name="update_projects_issues")
     */
    public function updateProjectIssues(Project $project): Response
    {
        $this->updateIssues($project);

        if ($this->issueUpdated > 0) {
            $this->addFlash('success', "$this->issueUpdated issues have been updated.");
        }

        if ($this->issueInserted > 0) {
            $this->addFlash('success', "$this->issueInserted issues have been added.");
        }

        return $this->redirectToRoute('view_project', ['id' => $project->getId()]);
    }

    /**
     * @Route("/project/{id}/export-to-pdf", name="export_project_to_pdf")
     */
    public function exportProjectToPdfAction(Project $project): Response
    {
        $issues = $this->getDoctrine()->getRepository(Issue::class)->findTimedIssuesRelatedToProject($project);

        $html = $this->renderView('default/pdf_project_issues.html.twig', array(
            'issues'  => $issues
        ));

        return new PdfResponse(
            $this->pdf->getOutputFromHtml($html),
            'issues.pdf'
        );
    }

    private function gitlabTimeToW3CTime(string $gitlabTime): DateTime
    {
        /**
         * Gitlab uses RFC3339_EXTENDED date format
         * 'Y-m-d\TH:i:s.vP'
         * We have to transform it into a much simplier ('Y-m-d\TH:i:s') format
         */
        $date = new DateTime($gitlabTime);
        return new DateTime($date->format('Y-m-d\TH:i:s'));
    }

    private function orderProjectsByName(array $gitlabProjects, ObjectRepository $repository): array
    {
        $projects = [];
        foreach ($gitlabProjects as $project) {
            $projects[] = [
                'gitlabId' => $project->id,
                'name' => $project->name,
                'avatarUrl' => $project->avatar_url,
                'associated' => $repository->findOneBy(['gitlabId' => $project->id]),
            ];
        }

        usort($projects, function ($val1, $val2) {
            if ($val1['name'] > $val2['name']) {
                return 1;
            }
            return -1;
        });
        return $projects;
    }

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

    private function updateIssues(Project $project, ?Milestone $milestone = null): void
    {
        $gitlabProjectIssues = $this->gitlabRequestService->getProjectsIssues($project, $milestone);

        $this->issueUpdated = 0;
        $this->issueInserted = 0;
        foreach ($gitlabProjectIssues as $gitlabProjectIssue) {
            $issue = $this->updateIssue($project, $gitlabProjectIssue);
            $this->updateNotes($issue);
            $this->entityManager->flush();
        }
    }

    private function updateIssue(Project $project, $gitlabIssue): Issue
    {
        $issue = $this->entityManager->getRepository(Issue::class)
            ->findOneBy(['gitlabId' => $gitlabIssue->id]);

        if ($issue === null) {
            $issue = new Issue(
                $gitlabIssue->title,
                $gitlabIssue->id,
                $gitlabIssue->iid,
                $project,
                $this->gitlabTimeToW3CTime($gitlabIssue->created_at),
                $this->gitlabTimeToW3CTime($gitlabIssue->updated_at),
                $gitlabIssue->state,
                $gitlabIssue->time_stats->time_estimate,
                $gitlabIssue->time_stats->total_time_spent
            );

            $this->issueInserted++;
        } elseif ($issue instanceof Issue) {
            $lastUpdated = $this->gitlabTimeToW3CTime($gitlabIssue->updated_at);
            $updatedAt = $issue->getUpdatedAt();
            if ($updatedAt && $lastUpdated->getTimestamp() > $updatedAt->getTimestamp()) {
                $issue->update(
                    $gitlabIssue->state,
                    new DateTime($gitlabIssue->updated_at),
                    $gitlabIssue->time_stats->time_estimate,
                    $gitlabIssue->time_stats->total_time_spent
                );
                $this->issueUpdated++;
            }
        }

        $milestone = null;
        if ($gitlabIssue->milestone !== null) {
            $milestone = $this->entityManager->getRepository(Milestone::class)
                ->findOneBy(['gitlabId' => $gitlabIssue->milestone->id]);
        }
        $issue->setMilestone($milestone);

        $this->entityManager->persist($issue);

        return $issue;
    }

    private function updateNotes(Issue $issue): void
    {
        $gitlabIssuesNotes = $this->gitlabRequestService->getIssueNotes($issue);
        foreach ($gitlabIssuesNotes as $gitlabIssuesNote) {
            $note = $this->entityManager->getRepository(Note::class)
                ->findOneBy(['gitlabId' => $gitlabIssuesNote->id]);
            if ($note === null) {
                $note = new Note(
                    $gitlabIssuesNote->id,
                    $issue,
                    $gitlabIssuesNote->body,
                    $gitlabIssuesNote->author->id,
                    $gitlabIssuesNote->system,
                    $this->gitlabTimeToW3CTime($gitlabIssuesNote->created_at),
                    $this->gitlabTimeToW3CTime($gitlabIssuesNote->updated_at));
            } else {
                $lastUpdated = $this->gitlabTimeToW3CTime($gitlabIssuesNote->updated_at);
                $updatedAt = $note->getUpdatedAt();
                if ($updatedAt && $lastUpdated->getTimestamp() > $updatedAt->getTimestamp()) {
                    $note->setBody($gitlabIssuesNote->body);
                    $note->setAuthor($gitlabIssuesNote->author->id);
                    $note->setSystem($gitlabIssuesNote->system);
                    $note->setUpdatedAt($this->gitlabTimeToW3CTime($gitlabIssuesNote->updated_at));
                }
            }
            $this->entityManager->persist($note);
        }
    }

    private function updateMilestones(Project $project): void
    {
        $gitlabMilestones = $this->gitlabRequestService->getProjectsMilestones($project);

        foreach ($gitlabMilestones as $gitlabMilestone) {
            $milestone = $this->entityManager->getRepository(Milestone::class)
                ->findOneBy(['gitlabId' => $gitlabMilestone->id]);

            if ($milestone === null) {
                $milestone = new Milestone(
                    $gitlabMilestone->id,
                    $project,
                    $gitlabMilestone->title,
                    $this->gitlabTimeToW3CTime($gitlabMilestone->created_at),
                    $this->gitlabTimeToW3CTime($gitlabMilestone->updated_at),
                    $gitlabMilestone->due_date ? $this->gitlabTimeToW3CTime($gitlabMilestone->due_date) : null,
                    $gitlabMilestone->start_date ? $this->gitlabTimeToW3CTime($gitlabMilestone->start_date) : null,
                    $gitlabMilestone->state,
                    $gitlabMilestone->description
                );
                $this->entityManager->persist($milestone);
                $this->milestoneInserted++;
            } elseif ($milestone instanceof Milestone) {
                $lastUpdated = $this->gitlabTimeToW3CTime($gitlabMilestone->updated_at);
                $updatedAt = $milestone->getUpdatedAt();
                if ($updatedAt && $lastUpdated->getTimestamp() > $updatedAt->getTimestamp()) {
                    $milestone->update(
                        $gitlabMilestone->state,
                        $gitlabMilestone->title,
                        $gitlabMilestone->description,
                        $this->gitlabTimeToW3CTime($gitlabMilestone->updated_at),
                        $this->gitlabTimeToW3CTime($gitlabMilestone->due_date),
                        $this->gitlabTimeToW3CTime($gitlabMilestone->start_date)
                    );
                    $this->milestoneUpdated++;
                }
                $this->entityManager->persist($milestone);
            }
        }

        $this->entityManager->flush();
    }
}
