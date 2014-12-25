<?php

namespace Zoop\Entity;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Id\AbstractIdGenerator;
use Zoop\Entity\Slugger; //TODO: create this class

class EntitySlugGenerator extends AbstractIdGenerator
{
    /**
     *
     * @param \Doctrine\ODM\MongoDB\DocumentManager $documentManager
     * @param $entity
     * @return string|null
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function generate(DocumentManager $documentManager, $entity)
    {
        if ($name = $entity->getName()) {
            return Slugger::createSlug($name);
        }
    }
}
