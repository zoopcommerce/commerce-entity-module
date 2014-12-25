<?php

namespace Zoop\Entity\Test\Controller;

use Zend\Http\Header\Origin;
use Zend\Http\Header\Host;
use Zoop\Entity\Test\AbstractTest;
use Zoop\Entity\DataModel\Company;
use Zoop\Test\Helper\DataHelper;

class CustomerCrudTest extends AbstractTest
{
    public function testNoAuthorizationCreate()
    {
        $data = [
            "name" => "Nespresso",
            "email" => "info@nespresso.com",
            "phoneNumber" => "+1 1 800 562 1465",
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
                Origin::fromString('Origin: http://api.zoopcommerce.local'),
                Host::fromString('Host: api.zoopcommerce.local')
            ]);

        $this->dispatch('http://api.zoopcommerce.local/companies');
        $response = $this->getResponse();
        
        $this->assertResponseStatusCode(401);
    }
    
    public function testUnAuthorizedCreate()
    {
        $data = [
            "name" => "Nespresso",
            "email" => "info@nespresso.com",
            "phoneNumber" => "+1 1 800 562 1465",
            "address" => [
                "line1" => "24-01 44th Road, 12th Floor",
                "line2" => "",
                "city" => "Long Island City",
                "state" => "NY",
                "postcode" => "11101",
                "country" => "US"
            ]
        ];

        DataHelper::createCustomerUser(self::getNoAuthDocumentManager(), self::getDbName());

        $key = 'nespresso';
        $secret = 'password1';

        $request = $this->getRequest();
        $request->setContent(json_encode($data));
        
        $this->applyJsonRequest($request);
        $this->applyUserToRequest($request, $key, $secret);
        
        $request->setMethod('POST')
            ->getHeaders()->addHeaders([
                Origin::fromString('Origin: http://api.zoopcommerce.local'),
                Host::fromString('Host: api.zoopcommerce.local')
            ]);

        $this->dispatch('http://api.zoopcommerce.local/companies');
        $response = $this->getResponse();
        
        $this->assertResponseStatusCode(403);
    }

    public function testCreateSuccess()
    {
        $name = "Nespresso";
        $data = [
            "name" => $name,
            "email" => "info@nespresso.com",
            "phoneNumber" => "+1 1 800 562 1465",
            "address" => [
                "line1" => "24-01 44th Road, 12th Floor",
                "line2" => "",
                "city" => "Long Island City",
                "state" => "NY",
                "postcode" => "11101",
                "country" => "US"
            ]
        ];
        
        DataHelper::createPartnerUser(self::getNoAuthDocumentManager(), self::getDbName());
        DataHelper::createPartner(self::getNoAuthDocumentManager(), self::getDbName());
        
        $data['partner'] = ['$ref' => '540101127f8b9ae8068b4567'];

        $key = 'bigspaceship';
        $secret = 'password1';

        $post = json_encode($data);
        $request = $this->getRequest();
        $request->setContent($post);
        
        $this->applyJsonRequest($request);
        $this->applyUserToRequest($request, $key, $secret);
        
        $request->setMethod('POST')
            ->getHeaders()->addHeaders([
                Origin::fromString('Origin: http://api.zoopcommerce.local'),
                Host::fromString('Host: api.zoopcommerce.local')
            ]);

        $this->dispatch('http://api.zoopcommerce.local/companies');
        $response = $this->getResponse();

        $this->assertResponseStatusCode(201);
        
        $companyId = str_replace(
            ['Location: ', '/companies/'],
            '',
            $response->getHeaders()->get('Location')->toString()
        );
        
        $this->assertNotNull($companyId);
        
        self::getNoAuthDocumentManager()->clear();
        
        $company = DataHelper::get(self::getNoAuthDocumentManager(), 'Zoop\Company\DataModel\Company', $companyId);
        $this->assertTrue($company instanceof Company);
        $this->assertEquals($name, $company->getName());
        
        return $companyId;
    }

    /**
     * @depends testCreateSuccess
     */
    public function testPatchSuccess($companyId)
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

        $key = 'bigspaceship';
        $secret = 'password1';

        $request = $this->getRequest();
        $request->setContent(json_encode($data));
        
        $this->applyJsonRequest($request);
        $this->applyUserToRequest($request, $key, $secret);
        
        $request->setMethod('PATCH')
            ->getHeaders()->addHeaders([
                Origin::fromString('Origin: http://api.zoopcommerce.local'),
                Host::fromString('Host: api.zoopcommerce.local')
            ]);

        $this->dispatch(sprintf('http://api.zoopcommerce.local/companies/%s', $companyId));
        $response = $this->getResponse();

        $this->assertResponseStatusCode(204);

        self::getNoAuthDocumentManager()->clear();

        $company = DataHelper::get(self::getNoAuthDocumentManager(), 'Zoop\Company\DataModel\Company', $companyId);
        
        $this->assertTrue($company instanceof Company);
        $this->assertEquals($name, $company->getName());
        $this->assertEquals('AU', $company->getAddress()->getCountry());
    }
    
    /**
     * @depends testCreateSuccess
     */
    public function testDeleteSuccess($companyId)
    {
        $key = 'bigspaceship';
        $secret = 'password1';

        $request = $this->getRequest();
        
        $this->applyJsonRequest($request);
        $this->applyUserToRequest($request, $key, $secret);
        
        $request->setMethod('DELETE')
            ->getHeaders()->addHeaders([
                Origin::fromString('Origin: http://api.zoopcommerce.local'),
                Host::fromString('Host: api.zoopcommerce.local')
            ]);

        $this->dispatch(sprintf('http://api.zoopcommerce.local/companies/%s', $companyId));
        $response = $this->getResponse();

        $this->assertResponseStatusCode(204);
        
        //we need to just do a soft delete rather than a hard delete
        self::getNoAuthDocumentManager()->clear();
        $company = DataHelper::get(self::getNoAuthDocumentManager(), 'Zoop\Company\DataModel\Company', $companyId);
        $this->assertEmpty($company);
    }
}
