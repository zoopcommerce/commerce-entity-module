<?php

/**
 * @package Zoop
 */

namespace Zoop\Entity;

use Zend\Mvc\MvcEvent;

/**
 * @author  Josh Stuart <josh.stuart@zoopcommerce.com>
 */
class Module
{
    /**
     * Adds a store filter listener
     *
     * @param \Zend\EventManager\Event $event
     */
    public function onBootstrap(MvcEvent $event)
    {
        $application = $event->getTarget();
        $serviceManager = $application->getServiceManager();

        //filter events
        $eventManager = $application->getEventManager();
        $eventManager->attach($serviceManager->get('zoop.commerce.entity.filterlistener'));

        //enforcer events
        $documentManager = $serviceManager->get('doctrine.odm.documentmanager.commerce');
        $dmEventManager = $documentManager->getEventManager();

        //enforce the current entity is stored on documents that contain an entity filter
        $dmEventManager->addEventSubscriber($serviceManager->get('zoop.commerce.entity.enforcersubscriber'));

        //ensure when entities are persisted a user event is fired
//        $dmEventManager->addEventSubscriber($serviceManager->get('zoop.commerce.entity.usersubscriber'));
    }

    /**
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/../../../config/module.config.php';
    }
}
