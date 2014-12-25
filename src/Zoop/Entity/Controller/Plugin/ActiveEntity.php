<?php

namespace Zoop\Entity\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * Controller plugin to fetch the active store
 */
class ActiveEntity extends AbstractPlugin
{
    protected $activeEntity;

    public function getActiveEntity()
    {
        return $this->activeEntity;
    }

    public function setActiveStore($activeEntity)
    {
        $this->activeEntity = $activeEntity;
    }

    /**
     *
     * @return mixed|null
     * @throws Exception\RuntimeException
     */
    public function __invoke()
    {
        return $this->activeEntity;
    }
}
