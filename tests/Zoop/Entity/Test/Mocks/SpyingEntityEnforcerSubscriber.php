<?php

namespace Zoop\Entity\Test\Mocks;

use Zoop\Entity\EntityEnforcerSubscriber;
use Zoop\Entity\DataModel\EntitiesFilterInterface;
use Zoop\Entity\DataModel\EntityFilterInterface;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Josh Stuart <josh.stuart@zoopcommerce.com>
 */
class SpyingEntityEnforcerSubscriber extends EntityEnforcerSubscriber
{
    private $doApplyEntityTrait = false;
    private $doApplyEntitiesTrait = false;

    public function getDoApplyEntityTrait()
    {
        return $this->doApplyEntityTrait;
    }

    public function getDoApplyEntitiesTrait()
    {
        return $this->doApplyEntitiesTrait;
    }

    protected function addEntityToDocument(EntityFilterInterface $document)
    {
        $this->doApplyEntityTrait = true;
    }

    protected function addEntitiesToDocument(EntitiesFilterInterface $document)
    {
        $this->doApplyEntitiesTrait = true;
    }
}
