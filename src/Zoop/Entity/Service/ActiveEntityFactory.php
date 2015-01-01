<?php

namespace Zoop\Entity\Service;

use \Exception;
use Zend\Http\Request as HttpRequest;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zoop\GomiModule\DataModel\User;
use Zoop\Entity\DataModel\Entity;
use Zoop\ShardModule\Exception\DocumentNotFoundException;
use Zoop\Shard\ODMCore\ModelManager;

class ActiveEntityFactory implements AbstractFactoryInterface
{
    const SYSTEM_ENTITY = 'sys::entity';
    protected $hosts = [];
    protected $systemUser;
    protected $activeUser;
    protected $isAllowOverride;

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param string $name
     * @param string $requestedName
     * @return boolean
     * @throws Exception
     * @throws DocumentNotFoundException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        if ($name != 'zoop.commerce.entity.active') {
            return false;
        }
        $request = $serviceLocator->get('request');

        if (!$request instanceof HttpRequest) {
            throw new Exception('The entity could not be found as it\'s missing a http request');
        }

        $host = $this->getHostFromRequest($request);

        if (!isset($host)) {
            throw new DocumentNotFoundException('The entity could not be found within the request host', 404);
        }
        $activeEntity = $this->loadEntity($host, $serviceLocator);

        if (is_null($activeEntity)) {
            throw new DocumentNotFoundException('The entity ' . $host . ' could not be found', 404);
        }

        return true;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param string $name
     * @param string $requestedName
     * @return Entity
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $request = $serviceLocator->get('request');
        $host = $this->getHostFromRequest($request);

        $activeEntity = $this->loadEntity($host, $serviceLocator);

        return $activeEntity;
    }

    /**
     * @param string $host
     * @param ServiceLocatorInterface $serviceLocator
     * @return Entity|null
     */
    protected function loadEntity($host, ServiceLocatorInterface $serviceLocator)
    {
        if (isset($this->hosts[$host])) {
            return $this->hosts[$host];
        }

        $documentManager = $serviceLocator->get('shard.commerce.modelmanager');
        $serviceManager = $serviceLocator->get('shard.commerce.servicemanager');
        /* @var $documentManager ModelManager */

        $queryBuilder = $documentManager
            ->createQueryBuilder()
            ->find('Zoop\Entity\DataModel\AbstractEntity')
            ->field('domains')->in([$host]);

        //Read access control needs to be turned off when fetching active entity
        $this->initSystemUser($serviceManager);

        //get the current entity
        $activeEntity = $queryBuilder->getQuery()->getSingleResult();

        //remove the temp user role
        $this->teardownSystemUser($serviceManager);

        $this->hosts[$host] = $activeEntity;

        return $activeEntity;
    }

    /**
     * @param Request $request
     * @return string
     */
    protected function getHostFromRequest(HttpRequest $request)
    {
        $origin = $request->getHeaders()->get('origin');
        if ($origin) {
            $host = str_replace(['http://', 'https://'], '', $origin->getFieldValue());
        } else {
            $host = $request->getUri()->getHost();
        }

        return $host;
    }

    /**
     * @param ServiceLocatorInterface $serviceManager
     */
    protected function initSystemUser(ServiceLocatorInterface $serviceManager)
    {
        $isAllowOverride = $serviceManager->getAllowOverride();
        $this->setAllowOverride($isAllowOverride);

        try {
            $activeUser = $serviceManager->get('user');
            if (!empty($activeUser)) {
                $this->setActiveUser($activeUser);
            }
        } catch (Exception $error) {

        }

        $serviceManager->setAllowOverride(true);
        $sysUser = new User;
        $sysUser->addRole(self::SYSTEM_ENTITY);
        $serviceManager->setService('user', $sysUser);

        $this->setSystemUser($sysUser);
    }

    protected function teardownSystemUser(ServiceLocatorInterface $serviceManager)
    {
        $activeUser = $this->getActiveUser();
        $this->getSystemUser()->removeRole(self::SYSTEM_ENTITY);
        if (!empty($activeUser)) {
            $serviceManager->setAllowOverride(true);
            $serviceManager->setService('user', $activeUser);
        }
        $serviceManager->setAllowOverride($this->isAllowOverride());
    }

    /**
     * @return User
     */
    public function getSystemUser()
    {
        return $this->systemUser;
    }

    /**
     * @param User $systemUser
     */
    public function setSystemUser(User $systemUser)
    {
        $this->systemUser = $systemUser;
    }

    /**
     * @return User
     */
    public function getActiveUser()
    {
        return $this->activeUser;
    }

    /**
     * @param User $activeUser
     */
    public function setActiveUser($activeUser)
    {
        $this->activeUser = $activeUser;
    }

    /**
     * @return boolean
     */
    public function isAllowOverride()
    {
        return $this->isAllowOverride;
    }

    /**
     * @param boolean $isAllowOverride
     */
    public function setAllowOverride($isAllowOverride)
    {
        $this->isAllowOverride = (boolean) $isAllowOverride;
    }
}
