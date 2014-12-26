<?php

namespace Zoop\Entity;

use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Http\Request as HttpRequest;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zoop\Entity\DataModel\EntityFilterInterface;
use Zoop\User\Events as UserEvents;

class EntityFilterListener implements ListenerAggregateInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = [];

    /**
     * Attach listeners to an event manager
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(UserEvents::USER_POST_AUTH, [$this, 'doUserStoreFilter'], 1);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, [$this, 'doOriginStoreFilter'], 1);
    }

    /**
     * Detach listeners from an event manager
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

    /**
     * Listen to the "user" event
     *
     * @param EventInterface $event
     * @return mixed
     */
    public function doUserStoreFilter(EventInterface $event)
    {
        $user = $event->getParams();
        if ($user instanceof EntityFilterInterface) {
            $serviceManager = $this->getServiceLocator();
            $manifest = $serviceManager->get('shard.commerce.manifest');
            $zone = $manifest->getServiceManager()->get('extension.zone');

            $entities = $user->getEntities();
            $zone->setReadFilterInclude($entities);
        }
    }

    /**
     * Listen to the route event and check for the origin header and init
     * the active entity to filter documents.
     *
     * @param EventInterface $event
     * @return mixed
     */
    public function doOriginStoreFilter(EventInterface $event)
    {
        $serviceManager = $this->getServiceLocator();

        $request = $serviceManager->get('request');
        if ($request instanceof HttpRequest) {
            $origin = $request->getHeaders()->get('origin');
            if ($origin) {
                $manifest = $serviceManager->get('shard.commerce.manifest');
                $zone = $manifest->getServiceManager()->get('extension.zone');

                $entity = $serviceManager->get('zoop.commerce.entity.active');
                $zone->setReadFilterInclude([$entity->getId()]);
            }
        }
    }
}
