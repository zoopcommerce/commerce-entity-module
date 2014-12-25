<?php

namespace Zoop\Entity\Test\Mocks;

use Zoop\Entity\EntityEnforcerSubscriber;
use Zoop\Entity\DataModel\EntityTraitInterface;
use Zoop\Entity\DataModel\EntitiesTraitInterface;

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

    protected function applyEntityTrait(EntityTraitInterface $document)
    {
        $this->doApplyEntityTrait = true;
    }

    protected function applyEntitiesTrait(EntitiesTraitInterface $document)
    {
        $this->doApplyEntitiesTrait = true;
    }
}
