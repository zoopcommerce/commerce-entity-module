<?php

namespace Zoop\Entity;

use \Exception;
use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zoop\Entity\DataModel\EntitiesFilterInterface;
use Zoop\Entity\DataModel\EntityFilterInterface;
use Zoop\Entity\Exception\MissingEntityFilterException;
use Zoop\User\DataModel\UserInterface;

class EntityEnforcerSubscriber implements
    EventSubscriber,
    ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public function getSubscribedEvents()
    {
        return array(
            'prePersist'
        );
    }

    /**
     * Listen to the "create" and "update" event to ensure
     * that if we have a zone filter on the entity, it is correctly enforced
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $document = $args->getDocument();

        if ($document instanceof EntityFilterInterface) {
            $this->addEntityToDocument($document);
        } elseif ($document instanceof EntitiesFilterInterface) {
            $this->addEntitiesToDocument($document);
        }
        //TODO apply the store/s filter
    }

    protected function addDocumentIdToFilter(EntitiesFilterInterface $document)
    {
        $document->addEntity($document->getId());
    }

    protected function addEntityToDocument(EntityFilterInterface $document)
    {
        //check to see if we have an active entity
        $entity = $this->getActiveEntity();
        if ($entity !== false) {
            //if so check the existing entity in the document with the active entity
            if ($document->getEntity() != $entity->getSlug()) {
                $document->setEntity($entity->getSlug());
            }
        } else {
            //if not, check the user for allowed entities
            $this->addUserEntityToDocument($document);
        }
    }

    protected function addEntitiesToDocument(EntitiesFilterInterface $document)
    {
        $entity = $this->getActiveEntity();
        if ($entity !== false) {
            //only apply the entity if the active entity
            //is also an entity filter
            if ($entity instanceof EntitiesFilterInterface) {
                $document->addEntity($entity->getId());
            }
        } else {
            //if not, check the user for allowed entities
            $this->addUserEntityToDocument($document);
        }

        //ensure the document ID was also added to the filter
        $this->addDocumentIdToFilter($document);
    }

    protected function addUserEntityToDocument($document)
    {
        $user = $this->getUser();
        if ($user instanceof UserInterface && $user instanceof EntitiesFilterInterface) {
            $entities = $user->getEntities();
            if (!empty($entities)) {
                if ($document instanceof EntityFilterInterface) {
                    $document->setEntity($entities[0]);
                } elseif ($document instanceof EntitiesFilterInterface) {
                    $document->setEntities($entities);
                }
            } else {
                throw new MissingEntityFilterException('This document requires a entity filter');
            }
        } else {
            //if not, throw an error
            throw new MissingEntityFilterException('This document requires an entity filter');
        }
    }

    /**
     * Gets the current active entity or returns false
     * @return EntityInterface
     */
    protected function getActiveEntity()
    {
        try {
            return $this->getServiceLocator()
                ->get('zoop.commerce.entity.active');
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Gets the current active user or returns false
     * @return mixed|boolean
     */
    protected function getUser()
    {
        try {
            return $this->getServiceLocator()
                ->get('user');
        } catch (Exception $e) {
            return false;
        }
    }
}
