<?php

namespace Zoop\Store\Test\Assets;

use Zoop\Store\DataModel\StoresTrait;
use Zoop\Store\DataModel\StoresTraitInterface;
//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 * @ODM\Document
 */
class Product implements StoresTraitInterface
{
    use StoresTrait;

    /**
     * @ODM\Id
     */
    protected $id;

    /**
     * @ODM\String
     */
    protected $name;

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }
}
