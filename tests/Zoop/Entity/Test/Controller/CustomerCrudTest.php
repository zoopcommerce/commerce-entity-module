<?php

namespace Zoop\Entity\Test\Controller;

use Zend\Http\Header\Origin;
use Zend\Http\Header\Host;
use Zoop\Entity\Test\AbstractTest;
use Zoop\Customer\DataModel\Customer;
use Zoop\Test\Helper\DataHelper;

class CustomerCrudTest extends AbstractTest
{
    public function testNoAuthorizationCreate()
    {
        DataHelper::createPartner(self::getNoAuthDocumentManager(), self::getDbName());
        
        $data = [
            "name" => "Nespresso",
            "email" => "info@nespresso.com",
            "phoneNumber" => "+1 1 800 562 1465",
            "primaryDomain" => "nestle.com",
            "domains" => [
                "nestle.com"
            ],
            "address" => [
                "line1" => "24-01 44th Road, 12th Floor",
                "line2" => "",
                "city" => "Long Island City",
                "state" => "NY",
                "postcode" => "11101",
                "country" => "US"
            ]
        ];

        $request = $this->getRequest();
        $request->setContent(json_encode($data));

        $this->applyJsonRequest($request);

        $request->setMethod('POST')
            ->getHeaders()->addHeaders([
                Origin::fromString('Origin: http://bigspaceship.com'),
                Host::fromString('Host: bigspaceship.com')
            ]);

        $this->dispatch('http://api.zoopcommerce.local/customers');
        $response = $this->getResponse();

        $this->assertResponseStatusCode(403);
    }

    public function testUnAuthorizedCreate()
    {
        DataHelper::createEntities(self::getNoAuthDocumentManager(), self::getDbName());
        DataHelper::createCustomerUser(self::getNoAuthDocumentManager(), self::getDbName());
        
        $data = [
            "name" => "Nespresso",
            "email" => "info@nespresso.com",
            "phoneNumber" => "+1 1 800 562 1465",
            "primaryDomain" => "nestle.zoopcommerce.local",
            "domains" => [
                "nestle.com"
            ],
            "address" => [
                "line1" => "24-01 44th Road, 12th Floor",
                "line2" => "",
                "city" => "Long Island City",
                "state" => "NY",
                "postcode" => "11101",
                "country" => "US"
            ]
        ];

        $key = 'nespresso';
        $secret = 'password1';

        $request = $this->getRequest();
        $request->setContent(json_encode($data));

        $this->applyJsonRequest($request);
        $this->applyUserToRequest($request, $key, $secret);

        $request->setMethod('POST')
            ->getHeaders()->addHeaders([
                Origin::fromString('Origin: http://bigspaceship.com'),
                Host::fromString('Host: bigspaceship.com')
            ]);

        $this->dispatch('http://api.zoopcommerce.local/customers');
        $response = $this->getResponse();

        $this->assertResponseStatusCode(403);
    }

    public function testCreateSuccess()
    {
        DataHelper::createPartnerUser(self::getNoAuthDocumentManager(), self::getDbName());
        
        $name = "Nespresso";
        $data = [
            "name" => $name,
            "email" => "info@nespresso.com",
            "phoneNumber" => "+1 1 800 562 1465",
            "primaryDomain" => "nestle.zoopcommerce.local",
            "domains" => [
                "nestle.com"
            ],
            "address" => [
                "line1" => "24-01 44th Road, 12th Floor",
                "line2" => "",
                "city" => "Long Island City",
                "state" => "NY",
                "postcode" => "11101",
                "country" => "US"
            ]
        ];

        $key = 'michaelebpwotz';
        $secret = 'password1';

        $post = json_encode($data);
        $request = $this->getRequest();
        $request->setContent($post);

        $this->applyJsonRequest($request);
        $this->applyUserToRequest($request, $key, $secret);

        $request->setMethod('POST')
            ->getHeaders()->addHeaders([
                Origin::fromString('Origin: http://bigspaceship.com'),
                Host::fromString('Host: bigspaceship.com')
            ]);

        $this->dispatch('http://api.zoopcommerce.local/customers');
        $response = $this->getResponse();

        $this->assertResponseStatusCode(201);

        $customerId = str_replace(
            ['Location: ', '/customers/'],
            '',
            $response->getHeaders()->get('Location')->toString()
        );

        $this->assertNotNull($customerId);

        self::getNoAuthDocumentManager()->clear();

        $customer = DataHelper::get(self::getNoAuthDocumentManager(), 'Zoop\Customer\DataModel\Customer', $customerId);
        $this->assertTrue($customer instanceof Customer);
        $this->assertEquals($name, $customer->getName());

        return $customerId;
    }
    
    /**
     * @depends testCreateSuccess
     */
    public function testGetListSuccess()
    {
        $key = 'michaelebpwotz';
        $secret = 'password1';

        $request = $this->getRequest();

        $this->applyJsonRequest($request);
        $this->applyUserToRequest($request, $key, $secret);

        $request->setMethod('GET')
            ->getHeaders()->addHeaders([
                Origin::fromString('Origin: http://bigspaceship.com'),
                Host::fromString('Host: bigspaceship.com')
            ]);

        $this->dispatch('http://api.zoopcommerce.local/customers');
        $response = $this->getResponse();
        
        $this->assertResponseStatusCode(200);
        $content = $response->getContent();
        $this->assertJson($content);
        
        $data = json_decode($content, true);
        $this->assertCount(1, $data);
        
        $customer = $data[0];
        $this->assertEquals('Nespresso', $customer['name']);
        $this->assertEquals('nestle.zoopcommerce.local', $customer['primaryDomain']);

        self::getNoAuthDocumentManager()->clear();
    }

    /**
     * @depends testCreateSuccess
     */
    public function testPatchSuccess($customerId)
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

        $key = 'michaelebpwotz';
        $secret = 'password1';

        $request = $this->getRequest();
        $request->setContent(json_encode($data));

        $this->applyJsonRequest($request);
        $this->applyUserToRequest($request, $key, $secret);

        $request->setMethod('PATCH')
            ->getHeaders()->addHeaders([
                Origin::fromString('Origin: http://bigspaceship.com'),
                Host::fromString('Host: bigspaceship.com')
            ]);

        $this->dispatch(sprintf('http://api.zoopcommerce.local/customers/%s', $customerId));
        $response = $this->getResponse();

        $this->assertResponseStatusCode(204);

        self::getNoAuthDocumentManager()->clear();

        $customer = DataHelper::get(self::getNoAuthDocumentManager(), 'Zoop\Customer\DataModel\Customer', $customerId);

        $this->assertTrue($customer instanceof Customer);
        $this->assertEquals($name, $customer->getName());
        $this->assertEquals('AU', $customer->getAddress()->getCountry());
    }

    /**
     * @depends testCreateSuccess
     */
    public function testDeleteSuccess($customerId)
    {
        $key = 'michaelebpwotz';
        $secret = 'password1';

        $request = $this->getRequest();

        $this->applyJsonRequest($request);
        $this->applyUserToRequest($request, $key, $secret);

        $request->setMethod('DELETE')
            ->getHeaders()->addHeaders([
                Origin::fromString('Origin: http://bigspaceship.com'),
                Host::fromString('Host: bigspaceship.com')
            ]);

        $this->dispatch(sprintf('http://api.zoopcommerce.local/customers/%s', $customerId));
        $response = $this->getResponse();

        $this->assertResponseStatusCode(204);

        //we need to just do a soft delete rather than a hard delete
        self::getNoAuthDocumentManager()->clear();
        $customer = DataHelper::get(self::getNoAuthDocumentManager(), 'Zoop\Customer\DataModel\Customer', $customerId);
        $this->assertEmpty($customer);
    }
}
