<?php

namespace Zoop\Entity\Test\Service;

use Zoop\Entity\Test\AbstractTest;
use Zend\Http\Request;
use Zoop\Test\Helper\DataHelper;

class ActiveEntityTest extends AbstractTest
{
    public function testGetActiveEntity()
    {
        DataHelper::createStores(self::getNoAuthDocumentManager(), self::getDbName());

        $request = $this->getApplicationServiceLocator()->get('request');
        /* @var $request Request */
        $request->getUri()->setHost('apple.zoopcommerce.local');

        $store = $this->getApplicationServiceLocator()
            ->get('zoop.commerce.entity.active');

        $this->assertInstanceOf('Zoop\Store\DataModel\Store', $store);
        $this->assertEquals('Apple', $store->getName());
        $this->assertEquals('apple', $store->getSlug());
    }

    public function testRejectActiveStore()
    {
        $request = $this->getApplicationServiceLocator()->get('request');
        /* @var $request Request */
        $request->getUri()->setHost('missing.zoopcommerce.local');

        $this->setExpectedException('\Exception');
        $store = $this->getApplicationServiceLocator()->get('zoop.commerce.entity.active');
    }
}
