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

    public function onSetupMenu(SidebarMenuEvent $event)
    {
        $menu = new MenuItemModel(
            'update-projects-list-id',
            'Update projects list',
            'update_projects',
            [],
            'fas fa-sync'
        );

        $event->addItem($menu);

        $projects = $this->entityManager->getRepository(Project::class)
            ->findBy([], ['name' => 'ASC']);

        foreach ($projects as $project) {
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

        $this->activateByRoute(
            $event->getRequest()->get('_route'),
            $event->getItems()
        );
    }

    /**
     * @param string $route
     * @param MenuItemModel[] $items
     */
    protected function activateByRoute($route, $items)
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