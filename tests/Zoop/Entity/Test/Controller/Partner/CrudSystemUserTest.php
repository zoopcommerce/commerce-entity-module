<?php

namespace Zoop\Entity\Test\Controller\Partner;

use Zend\Http\Header\Origin;
use Zend\Http\Header\Host;
use Zoop\Entity\Test\AbstractTest;
use Zoop\Partner\DataModel\Partner;
use Zoop\Test\Helper\DataHelper;

class CrudSystemUserTest extends AbstractTest
{
    const USER_KEY = 'joshstuart';
    const USER_SECRET = 'password1';

    public function testNoAuthorizationCreate()
    {
        DataHelper::createEntities(self::getNoAuthDocumentManager(), self::getDbName());

        $request = $this->getRequest();
        $request->setContent(json_encode([]));

        $this->applyJsonRequest($request);

        $request->setMethod('POST')
            ->getHeaders()->addHeaders([
                Origin::fromString('Origin: http://blanka.local'),
                Host::fromString('Host: blanka.local')
            ]);

        $this->dispatch('http://api.zoopcommerce.local/partners');

        $this->assertResponseStatusCode(403);
    }

    public function testCreateSuccess()
    {
        DataHelper::createZoopUser(self::getNoAuthDocumentManager(), self::getDbName());

        $name = "Big Spaceship";
        $data = [
            "name" => $name,
            "email" => "info@bigspaceship.com",
            "phoneNumber" => "+1 718 222 0281",
            "primaryDomain" => "bigspaceship.com",
            "domains" => [
                "bigspaceship.com"
            ],
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
                Origin::fromString('Origin: http://blanka.local'),
                Host::fromString('Host: blanka.local')
            ]);

        $this->dispatch('http://api.zoopcommerce.local/partners');
        $response = $this->getResponse();

        $this->assertResponseStatusCode(201);

        $partnerId = str_replace(
            ['Location: ', '/partners/'],
            '',
            $response->getHeaders()->get('Location')->toString()
        );

        $this->assertNotNull($partnerId);

        self::getNoAuthDocumentManager()->clear();

        $partner = DataHelper::get(self::getNoAuthDocumentManager(), 'Zoop\Partner\DataModel\Partner', $partnerId);
        $this->assertTrue($partner instanceof Partner);
        $this->assertEquals($name, $partner->getName());

        return $partnerId;
    }

    /**
     * @depends testCreateSuccess
     */
    public function testGetListSuccess()
    {
        $request = $this->getRequest();

        $this->applyJsonRequest($request);
        $this->applyUserToRequest($request, self::USER_KEY, self::USER_SECRET);

        $request->setMethod('GET')
            ->getHeaders()->addHeaders([
                Origin::fromString('Origin: http://blanka.local'),
                Host::fromString('Host: blanka.local')
            ]);

        $this->dispatch('http://api.zoopcommerce.local/partners');
        $response = $this->getResponse();

        $this->assertResponseStatusCode(200);
        $content = $response->getContent();
        $this->assertJson($content);

        $data = json_decode($content, true);
        $this->assertCount(2, $data);

        $partner = $data[0];
        $this->assertEquals('Authentic Entertainment', $partner['name']);
        $this->assertEquals('authenticentertainment.com.au', $partner['domains'][0]);

        $partner = $data[1];
        $this->assertEquals('Big Spaceship', $partner['name']);
        $this->assertEquals('bigspaceship.com', $partner['domains'][0]);
    }

    /**
     * @depends testCreateSuccess
     */
    public function testGetSuccess($partnerId)
    {
        $request = $this->getRequest();

        $this->applyJsonRequest($request);
        $this->applyUserToRequest($request, self::USER_KEY, self::USER_SECRET);

        $request->setMethod('GET')
            ->getHeaders()->addHeaders([
                Origin::fromString('Origin: http://blanka.local'),
                Host::fromString('Host: blanka.local')
            ]);

        $this->dispatch(sprintf('http://api.zoopcommerce.local/partners/%s', $partnerId));
        $response = $this->getResponse();

        $this->assertResponseStatusCode(200);
        $content = $response->getContent();
        $this->assertJson($content);

        $data = json_decode($content, true);
        $this->assertEquals('Big Spaceship', $data['name']);
        $this->assertEquals('bigspaceship.com', $data['domains'][0]);
    }

    /**
     * @depends testCreateSuccess
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
                Origin::fromString('Origin: http://blanka.local'),
                Host::fromString('Host: blanka.local')
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
     * @depends testCreateSuccess
     */
    public function testDeleteSuccess($partnerId)
    {
        $request = $this->getRequest();

        $this->applyJsonRequest($request);
        $this->applyUserToRequest($request, self::USER_KEY, self::USER_SECRET);

        $request->setMethod('DELETE')
            ->getHeaders()->addHeaders([
                Origin::fromString('Origin: http://blanka.local'),
                Host::fromString('Host: blanka.local')
            ]);

        $this->dispatch(sprintf('http://api.zoopcommerce.local/partners/%s', $partnerId));

        $this->assertResponseStatusCode(204);

        //we need to just do a soft delete rather than a hard delete
        self::getNoAuthDocumentManager()->clear();

        $partner = DataHelper::get(self::getNoAuthDocumentManager(), 'Zoop\Partner\DataModel\Partner', $partnerId);
        $this->assertNotEmpty($partner);
        $this->assertTrue($this->isSoftDeleted($partner));
    }
}
