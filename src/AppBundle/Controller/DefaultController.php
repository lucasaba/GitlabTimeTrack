<?php

namespace AppBundle\Controller;

use AppBundle\Entity\GitlabResponse;
use AppBundle\Entity\Issue;
use AppBundle\Entity\Project;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
        $client   = $this->get('eight_points_guzzle.client.api_gitlab');
        $nextPage = 1;
        $em = $this->getDoctrine()->getManager();
        $updated = 0;
        $inserted = 0;

        while ($nextPage > 0) {
            $gitlabResponse = new GitlabResponse($client->get('projects/'.$project->getGitlabId().'/issues', [
                'query' => [
                    'page' => $nextPage
                ]
            ]));

            foreach ($gitlabResponse->getArrayContent() as $issue) {

                $newIssue = $em->getRepository(Issue::class)
                    ->findOneBy(['gitlabId' => $issue->id]);

                if($newIssue == null) {
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
                    if($lastUpdated->diff($newIssue->getUpdatedAt())->s > 2) {
                        $newIssue->setUpdatedAt(new \DateTime($issue->updated_at))
                            ->setStatus($issue->state)
                            ->setTimeEstimate($issue->time_stats->time_estimate)
                            ->setTotalTimeSpent($issue->time_stats->total_time_spent);
                        $em->persist($newIssue);
                        $updated++;
                    }

                }
            }
            $nextPage = $gitlabResponse->hasNext();
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
}
