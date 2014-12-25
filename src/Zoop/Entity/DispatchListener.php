<?php

namespace Zoop\Entity;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Mvc\MvcEvent;

class DispatchListener implements ListenerAggregateInterface
{
    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();

    /**
     * Attach listeners to an event manager
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'onDispatch'), 100);
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
     * Listen to the "dispatch" event to check for the entity param
     *
     * @param  MvcEvent $event
     * @return mixed
     */
    public function onDispatch(MvcEvent $event)
    {
        $serviceManager = $event->getApplication()->getServiceManager();
        $manifest = $serviceManager->get('shard.commerce.manifest');
        $zoneExtension = $manifest->getServiceManager()->get('extension.zone');

        $subdomain = $event->getRouteMatch()->getParam('entity');
        if (!empty($subdomain)) {
            $zoneExtension->setReadFilterInclude([$subdomain]);
        }
    }
}
