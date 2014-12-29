<?php

namespace Zoop\Entity\Test\Controller\Store;

use Zend\Http\Header\Origin;
use Zend\Http\Header\Host;
use Zoop\Store\DataModel\Store;
use Zoop\Entity\Test\AbstractTest;
use Zoop\Test\Helper\DataHelper;

class StoreCrudTest extends AbstractTest
{
    const USER_KEY = 'joshstuart';
    const USER_SECRET = 'password1';

    public function testNoAuthorizationCreate()
    {
        DataHelper::createEntities(self::getNoAuthDocumentManager(), self::getDbName());

        $data = [
            "slug" => "tesla",
            "name" => "Tesla",
            "primaryDomain" => "tesla.zoopcommerce.com",
            "domains" => [
                "tesla.zoopcommerce.com",
                "teslamotors.com"
            ],
            "email" => "info@teslamotors.com"
        ];

        $request = $this->getRequest();
        $request->setContent(json_encode($data));

        $this->applyJsonRequest($request);

        $request->setMethod('POST')
            ->getHeaders()->addHeaders([
                Origin::fromString('Origin: http://blanka.local'),
                Host::fromString('Host: blanka.local')
            ]);

        $this->dispatch('http://api.zoopcommerce.local/stores');

        $this->assertResponseStatusCode(403);
    }

    public function testCreateSuccess()
    {
        self::getDocumentManager()->clear();

        $slug = "tesla-au";
        $name = "Tesla - Australia";
        $data = [
            "slug" => $slug,
            "name" => $name,
            "primaryDomain" => "tesla.zoopcommerce.com",
            "domains" => [
                "tesla.zoopcommerce.com",
                "teslamotors.com.au"
            ],
            "email" => "info@teslamotors.com.au"
        ];

        DataHelper::createZoopUser(self::getNoAuthDocumentManager(), self::getDbName());

        $post = json_encode($data);
        $request = $this->getRequest();
        $request->setContent($post);

        $this->applyJsonRequest($request);
        $this->applyUserToRequest($request, self::USER_KEY, self::USER_SECRET);

        $request->setMethod('POST')
            ->getHeaders()->addHeaders([
                Origin::fromString('Origin: http://blanka.local'),
                Host::fromString('Host: blanka.local')
            ]);

        $this->dispatch('http://api.zoopcommerce.local/stores');
        $response = $this->getResponse();

        $this->assertResponseStatusCode(201);

        $storeId = str_replace(
            ['Location: ', '/stores/'],
            '',
            $response->getHeaders()->get('Location')->toString()
        );

        $this->assertNotNull($storeId);

        self::getNoAuthDocumentManager()->clear();

        $store = DataHelper::get(self::getNoAuthDocumentManager(), 'Zoop\Store\DataModel\Store', $storeId);
        $this->assertTrue($store instanceof Store);
        $this->assertEquals($name, $store->getName());

        return $storeId;
    }

    /**
     * @depends testCreateSuccess
     */
    public function testGetListSuccess($storeId)
    {
        self::getDocumentManager()->clear();

        $request = $this->getRequest();

        $this->applyJsonRequest($request);
        $this->applyUserToRequest($request, self::USER_KEY, self::USER_SECRET);

        $request->setMethod('GET')
            ->getHeaders()->addHeaders([
                Origin::fromString('Origin: http://blanka.local'),
                Host::fromString('Host: blanka.local')
            ]);

        $this->dispatch('http://api.zoopcommerce.local/stores');
        $response = $this->getResponse();

        $this->assertResponseStatusCode(200);

        $json = $response->getContent();
        $this->assertJson($json);

        $content = json_decode($json, true);

        $this->assertCount(6, $content);

        $store = $content[0];

        $this->assertEquals('tesla', $store['slug']);
        $this->assertEquals('Tesla', $store['name']);
        $this->assertEquals('info@teslamotors.com', $store['email']);
        $this->assertCount(2, $store['domains']);
    }

    /**
     * @depends testCreateSuccess
     */
    public function testGetSuccess($storeId)
    {
        self::getDocumentManager()->clear();

        $request = $this->getRequest();

        $this->applyJsonRequest($request);
        $this->applyUserToRequest($request, self::USER_KEY, self::USER_SECRET);

        $request->setMethod('GET')
            ->getHeaders()->addHeaders([
                Origin::fromString('Origin: http://blanka.local'),
                Host::fromString('Host: blanka.local')
            ]);

        $this->dispatch(sprintf('http://api.zoopcommerce.local/stores/%s', $storeId));
        $response = $this->getResponse();

        $this->assertResponseStatusCode(200);

        $json = $response->getContent();
        $this->assertJson($json);

        $store = json_decode($json, true);

        $this->assetNotNull($store);

        $this->assertEquals('tesla-au', $store['slug']);
        $this->assertEquals('Tesla - Australia', $store['name']);
        $this->assertEquals('info@teslamotors.com.au', $store['email']);
        $this->assertCount(2, $store['domains']);
    }

    /**
     * @depends testCreateSuccess
     */
    public function testPatchSuccess($storeId)
    {
        self::getDocumentManager()->clear();

        $name = "Tesla Pty Ltd";
        $data = [
            "name" => $name,
            "domains" => [
                "teslamotors.com",
                "teslamotors.com.au"
            ],
            "email" => "info@teslamotors.com.au"
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

        $this->dispatch(sprintf('http://api.zoopcommerce.local/stores/%s', $storeId));

        $this->assertResponseStatusCode(204);

        self::getNoAuthDocumentManager()->clear();

        $store = DataHelper::get(self::getNoAuthDocumentManager(), 'Zoop\Store\DataModel\Store', $storeId);

        $this->assertTrue($store instanceof Store);
        $this->assertEquals($name, $store->getName());
        $this->assertCount(2, $store->getDomains());
    }

    /**
     * @depends testCreateSuccess
     */
    public function testDeleteSuccess($storeId)
    {
        $request = $this->getRequest();

        $this->applyJsonRequest($request);
        $this->applyUserToRequest($request, self::USER_KEY, self::USER_SECRET);

        $request->setMethod('DELETE')
            ->getHeaders()->addHeaders([
                Origin::fromString('Origin: http://blanka.local'),
                Host::fromString('Host: blanka.local')
            ]);

        $this->dispatch(sprintf('http://api.zoopcommerce.local/stores/%s', $storeId));

        $this->assertResponseStatusCode(204);

        self::getNoAuthDocumentManager()->clear();

        $store = DataHelper::get(self::getNoAuthDocumentManager(), 'Zoop\Store\DataModel\Store', $storeId);
        $this->assertNotEmpty($store);
        $this->assertTrue($this->isSoftDeleted($store));
    }
}
