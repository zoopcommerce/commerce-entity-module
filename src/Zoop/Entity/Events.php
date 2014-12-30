<?php

namespace Zoop\Entity;

/**
 * Container for all Entity events.
 *
 * This class cannot be instantiated.
 *
 * @author      Josh Stuart <josh.stuart@zoopcommerce.com>
 */
final class Events
{
    /**
     * Private constructor to prevent class instantiation
     */
    private function __construct()
    {

    }

    /**
     * The entityPostPersist event occurs once an entity with a single
     * entity filter has been persisted
     *
     * @var string
     */
    const ENTITY_POST_PERSIST = 'entityPostPersist';

    /**
     * The entitiesPostPersist event occurs once an entity with a multiple
     * entity filter has been persisted
     *
     * @var string
     */
    const ENTITIES_POST_PERSIST = 'entitiesPostPersist';
}
