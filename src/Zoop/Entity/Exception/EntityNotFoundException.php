<?php

namespace Zoop\Entity\Exception;

/**
 * Could not find the entity
 */
class EntityNotFoundException extends \Exception implements ExceptionInterface
{
    protected $code = 404;
}
