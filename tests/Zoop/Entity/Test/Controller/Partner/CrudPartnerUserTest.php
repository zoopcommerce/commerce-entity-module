<?php

namespace Zoop\Entity\Test\Controller\Partner;

use Zend\Http\Header\Origin;
use Zend\Http\Header\Host;
use Zoop\Entity\Test\AbstractTest;
use Zoop\Partner\DataModel\Partner;
use Zoop\Test\Helper\DataHelper;

class CrudPartnerUserTest extends AbstractTest
{
    const USER_KEY = 'michaelebpwotz';
    const USER_SECRET = 'password1';
    const PARTNER_ID = 'bigspaceship';

    /**
     * @expectedException \Zoop\ShardModule\Exception\DocumentNotFoundException
     * @expectedExceptionCode 404
     */
    public function testPartnerNotFound()
    {
        DataHelper::createEntities(self::getNoAuthDocumentManager(), self::getDbName());

        $request = $this->getRequest();
        $request->setContent(json_encode([]));

        $this->applyJsonRequest($request);

        $request->setMethod('POST')
            ->getHeaders()->addHeaders([
                Origin::fromString('Origin: http://bigspaceship.com'),
                Host::fromString('Host: bigspaceship.com')
            ]);

        $this->dispatch('http://api.zoopcommerce.local/partners');
    }

    public function testCreateFail()
    {
        DataHelper::createPartnerUser(self::getNoAuthDocumentManager(), self::getDbName());
        DataHelper::createPartner(self::getNoAuthDocumentManager(), self::getDbName());
        self::getNoAuthDocumentManager()->clear();

        $name = "Big Spaceship";
        $data = [
            "name" => $name,
            "email" => "info@bigspaceship.com",
            "phoneNumber" => "+1 718 222 0281",
            "address" => [
                "line1" => "45 Main St. Suite 716",
                "line2" => "",
                "city" => "Brooklyn",
                "state" => "NY",
                "postcode" => "11201",
                "country" => "US"
            ]
        ];

        $request = $this->getRequest();
        $request->setContent(json_encode($data));

        $this->applyJsonRequest($request);
        $this->applyUserToRequest($request, self::USER_KEY, self::USER_SECRET);

        $request->setMethod('POST')
            ->getHeaders()->addHeaders([
                Origin::fromString('Origin: http://bigspaceship.com'),
                Host::fromString('Host: bigspaceship.com')
            ]);

        $this->dispatch('http://api.zoopcommerce.local/partners');
        $response = $this->getResponse();

        $this->assertResponseStatusCode(403);
    }

    public function testGetListSuccess()
    {
        $request = $this->getRequest();

        $this->applyJsonRequest($request);
        $this->applyUserToRequest($request, self::USER_KEY, self::USER_SECRET);

        $request->setMethod('GET')
            ->getHeaders()->addHeaders([
                Origin::fromString('Origin: http://bigspaceship.com'),
                Host::fromString('Host: bigspaceship.com')
            ]);

        $this->dispatch('http://api.zoopcommerce.local/partners');
        $response = $this->getResponse();

        $this->assertResponseStatusCode(200);
        $content = $response->getContent();
        $this->assertJson($content);

        $data = json_decode($content, true);
        $this->assertCount(1, $data);

        $partner = $data[0];

        $this->assertEquals('Big Spaceship', $partner['name']);
        $this->assertEquals('bigspaceship.com', $partner['domains'][0]);

        return $partner['slug'];
    }

    /**
     * @depends testGetListSuccess
     */
    public function testGetSuccess($partnerId)
    {
        $request = $this->getRequest();

        $this->applyJsonRequest($request);
        $this->applyUserToRequest($request, self::USER_KEY, self::USER_SECRET);

        $request->setMethod('GET')
            ->getHeaders()->addHeaders([
                Origin::fromString('Origin: http://bigspaceship.com'),
                Host::fromString('Host: bigspaceship.com')
            ]);

        $this->dispatch(sprintf('http://api.zoopcommerce.local/partners/%s', $partnerId));
        $response = $this->getResponse();

        $this->assertResponseStatusCode(200);
        $content = $response->getContent();
        $this->assertJson($content);

        $partner = json_decode($content, true);
        $this->assertEquals('Big Spaceship', $partner['name']);
        $this->assertEquals('bigspaceship.com', $partner['domains'][0]);
    }

    /**
     * @depends testGetListSuccess
     */
    public function testPatchSuccess($partnerId)
    {
        $name = "Big Spaceship 2";
        $data = [
            "name" => $name,
            "email" => "info@bigspaceship.com",
            "phoneNumber" => "+1 718 222 0281",
            "address" => [
                "line1" => "45 Main St. Suite 716",
                "line2" => "",
                "city" => "Brooklyn",
                "state" => "NY",
                "postcode" => "11201",
                "country" => "AU"
            ]
        ];

        $request = $this->getRequest();
        $request->setContent(json_encode($data));

        $this->applyJsonRequest($request);
        $this->applyUserToRequest($request, self::USER_KEY, self::USER_SECRET);

        $request->setMethod('PATCH')
            ->getHeaders()->addHeaders([
                Origin::fromString('Origin: http://bigspaceship.com'),
                Host::fromString('Host: bigspaceship.com')
            ]);

        $this->dispatch(sprintf('http://api.zoopcommerce.local/partners/%s', $partnerId));

        $this->assertResponseStatusCode(204);

        self::getNoAuthDocumentManager()->clear();

        $partner = DataHelper::get(self::getNoAuthDocumentManager(), 'Zoop\Partner\DataModel\Partner', $partnerId);
        $this->assertTrue($partner instanceof Partner);
        $this->assertEquals($name, $partner->getName());
        $this->assertEquals('AU', $partner->getAddress()->getCountry());
    }

    /**
     * @depends testGetListSuccess
     */
    public function testDeleteFailed($partnerId)
    {
        $request = $this->getRequest();

        $this->applyJsonRequest($request);
        $this->applyUserToRequest($request, self::USER_KEY, self::USER_SECRET);

        $request->setMethod('DELETE')
            ->getHeaders()->addHeaders([
                Origin::fromString('Origin: http://bigspaceship.com'),
                Host::fromString('Host: bigspaceship.com')
            ]);

        $this->dispatch(sprintf('http://api.zoopcommerce.local/partners/%s', $partnerId));

        $this->assertResponseStatusCode(403);
        self::getNoAuthDocumentManager()->clear();

        $partner = DataHelper::get(self::getNoAuthDocumentManager(), 'Zoop\Partner\DataModel\Partner', $partnerId);
        $this->assertNotEmpty($partner);
        $this->assertFalse($this->isSoftDeleted($partner));
    }
}
