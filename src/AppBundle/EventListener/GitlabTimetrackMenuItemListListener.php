<?php
/**
 * Questo file fa parte del progetto gitlab-timetrack.
 * Il codice è fornito senza alcuna garanzia e distribuito
 * con licenza di tipo open source.
 * Per le informazioni sui diritti e le informazioni sulla licenza
 * consultare il file LICENSE che deve essere distribuito
 * insieme a questo codice.
 *
 * (c) Luca Saba <lucasaba@gmail.com>
 *
 * Created by PhpStorm.
 * User: luca
 * Date: 10/11/17
 * Time: 16.06
 */

namespace AppBundle\EventListener;

use AppBundle\Entity\Project;
use Avanzu\AdminThemeBundle\Event\SidebarMenuEvent;
use Avanzu\AdminThemeBundle\Model\MenuItemModel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;

class GitlabTimetrackMenuItemListListener implements EventSubscriberInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function onSetupMenu(SidebarMenuEvent $event) {

        $request = $event->getRequest();

        foreach ($this->getMenu($request) as $item) {
            $event->addItem($item);
        }

    }

    protected function getMenu(Request $request) {
        $menuItems = array();

        $menuItems[] = new MenuItemModel(
            'update-projects-list',
            'Update projects list',
            'update_projects',
            array(),
            'fa fa-refresh'
        );

        $projects = $this->entityManager->getRepository('AppBundle:Project')
            ->findAllSortedByTitle();

        foreach ($projects as $project) {
            /**
             * @var $project Project
             */
            $menuItems[] = new MenuItemModel(
                    'project-'.$project->getGitlabId(),
                    $project->getName(),
                    'view_project',
                    array('id' => $project->getId()),
                    'fa fa-archive'
                );
        }

        return $this->activateByRoute($request->get('_route'), $menuItems);
    }

    protected function activateByRoute($route, $items) {

        foreach($items as $item) {
            /**
             * @var $item MenuItemModel
             */
            if($item->hasChildren()) {
                $this->activateByRoute($route, $item->getChildren());
            }
            else {
                if($item->getRoute() == $route) {
                    $item->setIsActive(true);
                }
            }
        }

        return $items;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            'theme.sidebar_setup_menu' => 'onSetupMenu'
        ];
    }
}
