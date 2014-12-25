<?php

namespace Zoop\Entity\Controller;

use Doctrine\ODM\MongoDB\Cursor;
use Doctrine\ODM\MongoDB\DocumentManager;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zoop\Common\User\UserInterface;
use Zoop\User\DataModel\Zoop\Admin as ZoopAdmin;
use Zoop\Entity\DataModel\EntitiesFilterInterface;
use Zoop\Entity\DataModel\EntityInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateUsersListener
{
    protected $documentManager;
    protected $serviceManager;

    public function __call($name, $args)
    {
        return $this->doUpdateUsers($args[0], $name);
    }

    /**
     * Adds the new store to all appropriate users so they can then
     * query the store data and products
     *
     * @param MvcEvent $event
     */
    protected function doUpdateUsers(MvcEvent $event)
    {
        $options = $event->getTarget()
            ->getOptions();

        $entity = $event->getResult()
            ->getModel();

        if ($entity instanceof EntityInterface) {
            $this->setDocumentManager($options->getModelManager());
            $this->setServiceManager($options->getManifest()->getServiceManager());

            $user = $this->getUser();
            if (!$user instanceof ZoopAdmin) {
                $this->updateUsers($entity, $user);
            }
        }
    }

    /**
     * Adds the new store slug to users
     *
     * @param EntityInterface $entity
     * @param UserInterface $apiUser
     */
    protected function updateUsers(EntityInterface $entity, UserInterface $apiUser)
    {
        $users = $this->getUsers($apiUser);
        foreach ($users as $user) {
            if ($user instanceof EntitiesFilterInterface) {
                $user->addEntity($entity->getSlug());
            }
        }
    }

    /**
     * Gets all users based on the api user
     * @param UserInterface $user
     * @return Cursor
     */
    protected function getUsers(UserInterface $user)
    {
        $qb = $this->getDocumentManager()
            ->createQueryBuilder('Zoop\User\DataModel\AbstractUserFilter');

        if ($user instanceof EntitiesFilterInterface) {
            $qb->field('entities')->in($user->getEntities());
        }

        $users = $qb->getQuery()
            ->execute();

        return $users;
    }

    /**
     * @return UserInterface
     */
    protected function getUser()
    {
        return $this->getServiceManager()->get('user');
    }

    /**
     * @return DocumentManager
     */
    protected function getDocumentManager()
    {
        return $this->documentManager;
    }

    /**
     * @param DocumentManager $documentManager
     */
    protected function setDocumentManager(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    /**
     * @return ServiceLocatorInterface
     */
    protected function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * @param ServiceLocatorInterface $serviceManager
     */
    protected function setServiceManager(ServiceLocatorInterface $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
}
