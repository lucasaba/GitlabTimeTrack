<?php

namespace AppBundle\Command;

use AppBundle\Entity\Issue;
use AppBundle\Entity\Project;
use AppBundle\Service\GitlabRequestService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GitlabUpdateIssuesCommand extends ContainerAwareCommand
{
    /**
     * @var $em \Doctrine\Common\Persistence\ObjectManager
     */
    private $em;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('gitlab:update-issues')
            ->setDescription('Updates all issues with time informations');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $projects = $this->em->getRepository('AppBundle:Project')->findAll();

        foreach ($projects as $project) {
            $this->fetchOrUpdateIssues($project, $output);
        }
        $output->writeln("All issues inserted or updated.");
    }

    private function fetchOrUpdateIssues(Project $project, OutputInterface $output)
    {
        $gitlabRequestService = $this->getContainer()->get(GitlabRequestService::class);

        foreach ($gitlabRequestService->getProjectsIssues($project) as $issue) {

            $newIssue = $this->em->getRepository(Issue::class)
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
                $this->em->persist($newIssue);

                $output->writeln("New issue fetched: ".$project->getName().' - '.$newIssue->getTitle());
            } else {
                // We have to test if the issue has been updated
                $lastUpdated = new \DateTime($issue->updated_at);
                if($lastUpdated->diff($newIssue->getUpdatedAt())->s > 2) {
                    $output->writeln("Updating issue: ".$project->getName().' - '.$newIssue->getTitle());
                    /**
                     * @var $newIssue Issue
                     */
                    $newIssue->setUpdatedAt(new \DateTime($issue->updated_at))
                        ->setStatus($issue->state)
                        ->setTimeEstimate($issue->time_stats->time_estimate)
                        ->setTotalTimeSpent($issue->time_stats->total_time_spent);
                    $this->em->persist($newIssue);
                }

            }
        }

        $this->em->flush();
    }
}
