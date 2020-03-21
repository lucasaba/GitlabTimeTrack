<?php

namespace AppBundle\Command;

use AppBundle\Entity\Project;
use AppBundle\Service\GitlabRequestService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GitlabFetchProjectsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('gitlab:fetch-projects')
            ->setDescription('Fetch all projects visible to the user');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $gitlabRequestService = $this->getContainer()->get(GitlabRequestService::class);
        $em = $this->getContainer()->get('doctrine')->getManager();


        foreach ($gitlabRequestService->getProjects() as $project) {
            $newProject = $em->getRepository(Project::class)
                ->findOneBy(['gitlabId' => $project->id]);

            if($newProject == null) {
                $newProject = new Project();
                $newProject->setName($project->name)
                    ->setGitlabId($project->id)
                    ->setAvatarUrl($project->avatar_url);
                $em->persist($newProject);

                $output->writeln("New project fetched: ".$project->name);
            }
        }


        $em->flush();
        $output->writeln("All project persisted");
    }
}
