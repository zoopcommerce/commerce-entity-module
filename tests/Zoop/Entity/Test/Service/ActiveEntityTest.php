<?php

namespace Zoop\Entity\Test\Service;

use Zoop\Entity\Test\AbstractTest;
use Zend\Http\Request;
use Zoop\Test\Helper\DataHelper;

class ActiveEntityTest extends AbstractTest
{
    public function testGetActivePartner()
    {
        DataHelper::createEntities(self::getNoAuthDocumentManager(), self::getDbName());

        $request = $this->getApplicationServiceLocator()->get('request');
        /* @var $request Request */
        $request->getUri()->setHost('authenticentertainment.com.au');

        $partner = $this->getApplicationServiceLocator()
            ->get('zoop.commerce.entity.active');

        $this->assertInstanceOf('Zoop\Partner\DataModel\Partner', $partner);
        $this->assertEquals('Authentic Entertainment', $partner->getName());
        $this->assertEquals('authenticentertainment', $partner->getSlug());
        $this->assertEquals('info@authenticentertainment.com.au', $partner->getEmail());
    }

    public function testGetActiveCustomer()
    {
        $request = $this->getApplicationServiceLocator()->get('request');
        /* @var $request Request */
        $request->getUri()->setHost('apple.com');

        $customer = $this->getApplicationServiceLocator()
            ->get('zoop.commerce.entity.active');

        $this->assertInstanceOf('Zoop\Customer\DataModel\Customer', $customer);
        $this->assertEquals('Apple', $customer->getName());
        $this->assertEquals('apple', $customer->getSlug());
        $this->assertEquals('info@apple.com', $customer->getEmail());
    }

    public function testGetActiveStore()
    {
        $request = $this->getApplicationServiceLocator()->get('request');
        /* @var $request Request */
        $request->getUri()->setHost('teslamotors.com');

        $store = $this->getApplicationServiceLocator()
            ->get('zoop.commerce.entity.active');

        $this->assertInstanceOf('Zoop\Store\DataModel\Store', $store);
        $this->assertEquals('Tesla', $store->getName());
        $this->assertEquals('tesla', $store->getSlug());
        $this->assertEquals('info@teslamotors.com', $store->getEmail());
    }

    /**
     * @expectedException \Exception
     */
    public function testRejectActiveStore()
    {
        $request = $this->getApplicationServiceLocator()->get('request');
        /* @var $request Request */
        $request->getUri()->setHost('missing.zoopcommerce.local');

        $this->getApplicationServiceLocator()->get('zoop.commerce.entity.active');
    }
}
