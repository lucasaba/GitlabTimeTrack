<?php

namespace App\EventSubscriber;

use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use KevinPapst\AdminLTEBundle\Event\SidebarMenuEvent;
use KevinPapst\AdminLTEBundle\Model\MenuItemModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MenuBuilderSubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SidebarMenuEvent::class => ['onSetupMenu', 100],
        ];
    }

    /**
     * @param SidebarMenuEvent $event
     */
    public function onSetupMenu(SidebarMenuEvent $event): void
    {
        $event->addItem(
            new MenuItemModel(
                'home-menu-id',
                'Dashboard',
                'home',
                [],
                'fas fa-tachometer-alt'
            )
        );

        $event->addItem(
            new MenuItemModel(
                'update-projects-list-id',
                'Update projects list',
                'update_projects',
                [],
                'fas fa-sync'
            )
        );

        $projects = $this->entityManager->getRepository(Project::class)
            ->findBy([], ['name' => 'ASC']);

        foreach ($projects as $project) {
            if (! $project instanceof Project) {
                continue;
            }
            /**
             * @var $project Project
             */
            $event->addItem(
                new MenuItemModel(
                    'project-'.$project->getGitlabId(),
                    $project->getName(),
                    'view_project',
                    array('id' => $project->getId()),
                    'fa fa-archive'
                )
            );
        }

        $request = $event->getRequest();
        if (null !== $request) {
            $this->activateByRoute(
                $request->get('_route'),
                $event->getItems()
            );
        }
    }

    /**
     * @param string $route
     * @param MenuItemModel[] $items
     */
    protected function activateByRoute($route, $items): void
    {
        foreach ($items as $item) {
            if ($item->hasChildren()) {
                $this->activateByRoute($route, $item->getChildren());
            } elseif ($item->getRoute() == $route) {
                $item->setIsActive(true);
            }
        }
    }
}
