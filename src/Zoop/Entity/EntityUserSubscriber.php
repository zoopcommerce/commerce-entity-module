<?php

namespace Zoop\Entity;

use \Exception;
use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zoop\Entity\DataModel\EntitiesFilterInterface;
use Zoop\Entity\DataModel\EntityFilterInterface;

class EntityUserSubscriber implements
    EventSubscriber,
    ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public function getSubscribedEvents()
    {
        return array(
            'postPersist'
        );
    }
}
