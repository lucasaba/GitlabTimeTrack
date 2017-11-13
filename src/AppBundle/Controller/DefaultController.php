<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Issue;
use AppBundle\Entity\Project;
use AppBundle\Form\Type\ChooseProjectsType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * Show all available projects on GitLab and let the user choose those
     * to be tracked
     *
     * @Route("/update-projects", name="update_projects")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function chooseProjectsAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository(Project::class);
        $gitlabRequest = $this->get('gitlabtimetrack.request_service');

        /**
         * We need to fetch all projects from our GitLab server
         */
        $gitlabProjects = $gitlabRequest->getProjects();
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
            if($val1['name'] > $val2['name']) {
                return 1;
            }
            return -1;
        });

        /**
         * Then we build a form based on the previous data
         */
        $form = $this->createForm(ChooseProjectsType::class, null, array(
            'gitlabProjects' => $projects
        ));

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            /**
             * Handling form submission
             */
            $em = $this->getDoctrine()->getManager();
            foreach ($projects as $gitlabProject) {
                $value = $form->get('project_'.$gitlabProject['gitlabId'])->getData();
                if($value === false && $gitlabProject['associated'] != null) {
                    /**
                     * The value was unchecked but the project is in the DB
                     * Remove the project
                     */
                    $em->remove($gitlabProject['associated']);
                } elseif ($value === true && $gitlabProject['associated'] == null) {
                    /**
                     * The value was checked and the project is not in the DB
                     * Persist the new Project
                     */
                    $project = new Project();
                    $project->setName($gitlabProject['name'])
                        ->setGitlabId($gitlabProject['gitlabId'])
                        ->setAvatarUrl($gitlabProject['avatarUrl']);
                    $em->persist($project);
                }
                /**
                 * If $value is unchecked and the project is not associated
                 * OR $value is checked and project is associated.....
                 * Nothing to do! Go on.
                 */
                $em->flush();
            }
            return $this->redirectToRoute('homepage');
        }

        return $this->render('default/update_projects.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/project/{id}/view", name="view_project")
     *
     * @param Project $project
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewProjectAction(Project $project)
    {
        $issues = $this->getDoctrine()->getRepository('AppBundle:Issue')->findTimedIssuesRelatedToProject($project);

        return $this->render('default/view_project_issues.html.twig', ['issues' => $issues, 'project' => $project]);
    }

    /**
     * @Route("/project/{id}/update-issues", name="update_projects_issues")
     *
     * @param Project $project
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateProjectIssues(Project $project)
    {
        $gitlabRequest = $this->get('gitlabtimetrack.request_service');

        /**
         * We need to fetch all project's issues from our GitLab server
         */
        $gitlabProjectIssues = $gitlabRequest->getProjectsIssues($project);

        $em = $this->getDoctrine()->getManager();
        $updated = 0;
        $inserted = 0;

        foreach ($gitlabProjectIssues as $issue) {
            $newIssue = $em->getRepository(Issue::class)
                ->findOneBy(['gitlabId' => $issue->id]);

            if ($newIssue == null) {
                // We have to insert a new issue
                $newIssue = new Issue();
                $newIssue->setTitle($issue->title)
                    ->setGitlabId($issue->id)
                    ->setIssueNumber($issue->iid)
                    ->setProject($project)
                    ->setCreatedAt(new \DateTime($issue->created_at))
                    ->setUpdatedAt(new \DateTime($issue->updated_at))
                    ->setStatus($issue->state)
                    ->setTimeEstimate($issue->time_stats->time_estimate)
                    ->setTotalTimeSpent($issue->time_stats->total_time_spent);
                $em->persist($newIssue);
                $inserted++;
            } else {
                // We have to test if the issue has been updated
                $lastUpdated = new \DateTime($issue->updated_at);
                /**
                 * @var $newIssue Issue
                 */
                if ($lastUpdated->diff($newIssue->getUpdatedAt())->s > 2) {
                    $newIssue->setStatus($issue->state)
                        ->setUpdatedAt(new \DateTime($issue->updated_at))
                        ->setTimeEstimate($issue->time_stats->time_estimate)
                        ->setTotalTimeSpent($issue->time_stats->total_time_spent);
                    $em->persist($newIssue);
                    $updated++;
                }
            }
        }

        $em->flush();

        if($updated > 0) {
            $this->addFlash('success', "$updated issues have been updated.");
        }

        if($inserted > 0) {
            $this->addFlash('success', "$inserted issues have been added.");
        }

        return $this->redirectToRoute('view_project', ['id' => $project->getId()]);
    }

    /**
     * @Route("/clear-projects-cache", name="clear_projects_cache")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function clearProjectsCacheAction()
    {
        $cache = new FilesystemCache();
        $cache->delete('gitlab.projects_list');

        return $this->redirectToRoute('update_projects');
    }
}
