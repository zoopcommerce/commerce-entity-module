<?php

namespace Zoop\Entity\Service;

use Zoop\Entity\Controller\Plugin\ActiveEntity;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ActiveEntityPluginFactory implements AbstractFactoryInterface
{
    protected $hosts = [];

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param type $name
     * @param type $requestedName
     * @return boolean
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        if ($name != 'zoop.commerce.entity.active') {
            return false;
        }
        return $serviceLocator->getServiceLocator()->has('zoop.commerce.entity.active');
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param type $name
     * @param type $requestedName
     * @return ActiveEntity
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $instance = new ActiveEntity;
        $instance->setActiveEntity($serviceLocator->getServiceLocator()->get('zoop.commerce.entity.active'));
        return $instance;
    }
}
