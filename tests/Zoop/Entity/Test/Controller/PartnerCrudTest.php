<?php

namespace Zoop\Entity\Test\Controller;

use Zend\Http\Header\Origin;
use Zend\Http\Header\Host;
use Zoop\Entity\Test\AbstractTest;
use Zoop\Partner\DataModel\Partner;
use Zoop\Test\Helper\DataHelper;

class PartnerCrudTest extends AbstractTest
{
    public function testNoAuthorizationCreate()
    {
        DataHelper::createEntities(self::getNoAuthDocumentManager(), self::getDbName());

        $data = [
            "name" => "Big Spaceship",
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

        $request->setMethod('POST')
            ->getHeaders()->addHeaders([
                Origin::fromString('Origin: http://blanka.com'),
                Host::fromString('Host: blanka.com')
            ]);

        $this->dispatch('http://api.zoopcommerce.local/partners');
        $response = $this->getResponse();

        //TODO: CHANGE THIS TO 401
        $this->assertResponseStatusCode(403);
    }

    public function testUnAuthorizedCreate()
    {
        $data = [
            "name" => "Big Spaceship",
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

        DataHelper::createPartnerUser(self::getNoAuthDocumentManager(), self::getDbName());

        $key = 'bigspaceship';
        $secret = 'password1';

        $request = $this->getRequest();
        $request->setContent(json_encode($data));

        $this->applyJsonRequest($request);
        $this->applyUserToRequest($request, $key, $secret);

        $request->setMethod('POST')
            ->getHeaders()->addHeaders([
                Origin::fromString('Origin: http://blanka.com'),
                Host::fromString('Host: blanka.com')
            ]);

        $this->dispatch('http://api.zoopcommerce.local/partners');
        $response = $this->getResponse();

        $this->assertResponseStatusCode(403);
    }

    public function testCreateSuccess()
    {
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

        DataHelper::createZoopUser(self::getNoAuthDocumentManager(), self::getDbName());

        $key = 'joshstuart';
        $secret = 'password1';

        $request = $this->getRequest();
        $request->setContent(json_encode($data));

        $this->applyJsonRequest($request);
        $this->applyUserToRequest($request, $key, $secret);

        $request->setMethod('POST')
            ->getHeaders()->addHeaders([
                Origin::fromString('Origin: http://blanka.com'),
                Host::fromString('Host: blanka.com')
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

        $key = 'joshstuart';
        $secret = 'password1';

        $request = $this->getRequest();
        $request->setContent(json_encode($data));

        $this->applyJsonRequest($request);
        $this->applyUserToRequest($request, $key, $secret);

        $request->setMethod('PATCH')
            ->getHeaders()->addHeaders([
                Origin::fromString('Origin: http://blanka.com'),
                Host::fromString('Host: blanka.com')
            ]);

        $this->dispatch(sprintf('http://api.zoopcommerce.local/partners/%s', $partnerId));
        $response = $this->getResponse();

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
        $key = 'joshstuart';
        $secret = 'password1';

        $request = $this->getRequest();

        $this->applyJsonRequest($request);
        $this->applyUserToRequest($request, $key, $secret);

        $request->setMethod('DELETE')
            ->getHeaders()->addHeaders([
                Origin::fromString('Origin: http://blanka.com'),
                Host::fromString('Host: blanka.com')
            ]);

        $this->dispatch(sprintf('http://api.zoopcommerce.local/partners/%s', $partnerId));
        $response = $this->getResponse();

        $this->assertResponseStatusCode(204);

        //we need to just do a soft delete rather than a hard delete
        self::getNoAuthDocumentManager()->clear();

        $partner = DataHelper::get(self::getNoAuthDocumentManager(), 'Zoop\Partner\DataModel\Partner', $partnerId);
        $this->assertEmpty($partner);
    }
}
