<?php

namespace Zoop\Entity;

use \Exception;
use Doctrine\Common\EventSubscriber;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zoop\Customer\DataModel\CustomerInterface;
use Zoop\Entity\DataModel\ChildEntityInterface;
use Zoop\Entity\DataModel\EntitiesFilterInterface;
use Zoop\Entity\DataModel\EntityFilterInterface;
use Zoop\Entity\DataModel\EntityInterface;
use Zoop\Entity\Events;
use Zoop\Entity\Exception\InvalidParentException;
use Zoop\Entity\Exception\MissingEntityFilterException;
use Zoop\Entity\Exception\MissingParentException;
use Zoop\Partner\DataModel\PartnerInterface;
use Zoop\Store\DataModel\StoreInterface;
use Zoop\ShardModule\Exception\AccessControlException;
use Zoop\User\DataModel\UserInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EntityEnforcerSubscriber implements
    EventSubscriber,
    ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public function getSubscribedEvents()
    {
        return array(
            'postPersist',
            'prePersist'
        );
    }

    /**
     * After an entity has been persisted, shoot off an event so the
     * user module can update itself
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $document = $args->getDocument();
        if ($document instanceof EntityInterface) {
            $eventManager = $this->getServiceLocator()
                ->get('Application')
                ->getEventManager();

            if ($document instanceof EntityFilterInterface) {
                $eventManager->trigger(Events::ENTITY_POST_PERSIST, null, $document);
            } elseif ($document instanceof EntitiesFilterInterface) {
                $eventManager->trigger(Events::ENTITIES_POST_PERSIST, null, $document);
            }
        }
    }

    /**
     * Listen to the "create" and "update" event to ensure
     * that if we have a zone filter on the entity, it is correctly enforced
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $document = $args->getDocument();

        //add entities to document
        if ($document instanceof EntityFilterInterface) {
            $this->addEntityToDocument($document);
        } elseif ($document instanceof EntitiesFilterInterface) {
            $this->addEntitiesToDocument($document);
        }

        //TODO apply the store/s filter

        //add the parent entity to the document
        $this->addParentEntityToDocument($document);
    }

    /**
     * Adds the parent entity if it exists
     * @param mixed $document
     * @throws MissingParentException
     */
    protected function addParentEntityToDocument($document)
    {
        if ($document instanceof ChildEntityInterface) {
            $entity = $this->getActiveEntity();
            $parent = $document->getParent();

            if ($entity) {
                if (($document instanceof StoreInterface && $entity instanceof CustomerInterface) ||
                    ($document instanceof CustomerInterface && $entity instanceof PartnerInterface)
                ) {
                    $document->setParent($entity);
                } elseif (empty($parent)) {
                    throw new MissingParentException('Missing parent entity');
                }
            } else {
                if (empty($parent)) {
                    throw new MissingParentException('Missing parent entity');
                }
            }

            //validate parent
            $this->validateParent($document);
        }
    }

    /**
     * Adds the document entity to itself. This is to avoid a complex new
     * zone filter.
     *
     * @param EntitiesFilterInterface $document
     */
    protected function addDocumentIdToFilter(EntitiesFilterInterface $document)
    {
        $document->addEntity($document->getId());
    }

    /**
     * Adds an entity to the document base on the active entity
     * or the user.
     *
     * @param EntityFilterInterface $document
     */
    protected function addEntityToDocument(EntityFilterInterface $document)
    {
        //check to see if we have an active entity
        $entity = $this->getActiveEntity();
        if ($entity !== false) {
            //if so check the existing entity in the document with the active entity
            $documentEntity = $document->getEntity();
            if (empty($documentEntity)) {
                $document->setEntity($entity->getSlug());
            }
        } else {
            //if not, check the user for allowed entities
            $this->addUserEntityToDocument($document);
        }

        $this->validateEntity($document);
    }

    /**
     * Adds entities to the document base on the active entity
     * or the user.
     *
     * @param EntitiesFilterInterface $document
     */
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

        $this->validateEntities($document);

        //ensure the document ID was also added to the filter
        $this->addDocumentIdToFilter($document);
    }

    /**
     * Ensures that the document only contains an entity that the user is
     * authorized for.
     *
     * @param EntityFilterInterface $document
     * @throws AccessControlException
     */
    protected function validateEntity(EntityFilterInterface $document)
    {
        $user = $this->getUser();
        if ($user) {
            if (!in_array($document->getEntity(), $user->getEntities())) {
                throw new AccessControlException("Missing valid entity");
            }
        }
    }

    /**
     * Ensures that the document only contains entities that the user is
     * authorized for.
     *
     * @param EntitiesFilterInterface $document
     * @throws AccessControlException
     */
    protected function validateEntities(EntitiesFilterInterface $document)
    {
        $user = $this->getUser();
        if ($user) {
            foreach ($document->getEntities() as $entity) {
                if (!in_array($entity, $user->getEntities())) {
                    throw new AccessControlException("Missing valid entity");
                }
            }
        }
    }

    /**
     * Ensures that the parent that was added is a valid one
     *
     * @param ChildEntityInterface $document
     * @throws InvalidParentException
     * @throws MissingParentException
     */
    protected function validateParent(ChildEntityInterface $document)
    {
        $parent = $document->getParent();
        if (!empty($parent)) {
            $user = $this->getUser();
            if ($user && $user instanceof EntitiesFilterInterface) {
                if (!in_array($parent->getSlug(), $user->getEntities())) {
                    throw new InvalidParentException('Invalid parent entity');
                }
            }
        } else {
            throw new MissingParentException('Missing parent entity');
        }
    }

    /**
     * Adds the entity contained in the user, to the document
     *
     * @param type $document
     * @throws MissingEntityFilterException
     */
    protected function addUserEntityToDocument($document)
    {
        $user = $this->getUser();
        if ($user instanceof UserInterface && $user instanceof EntitiesFilterInterface) {
            $entities = $user->getEntities();
            if (!empty($entities)) {
                if ($document instanceof EntityFilterInterface) {
                    $documentEntity = $document->getEntity();
                    if (empty($documentEntity)) {
                        $document->setEntity($entities[0]);
                    }
                } elseif ($document instanceof EntitiesFilterInterface) {
                    $documentEntities = $document->getEntities();
                    if (empty($documentEntities)) {
                        $document->setEntities($entities);
                    }
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
     * @return EntitiesFilterInterface|boolean
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
