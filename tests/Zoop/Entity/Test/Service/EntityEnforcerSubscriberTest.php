<?php

namespace Zoop\Entity\Test\Service;

use Zoop\Entity\Exception\MissingEntityFilterException;
use Zoop\Entity\Test\AbstractTest;
use Zoop\Entity\Test\Mocks\SpyingEntityEnforcerSubscriber;
use Zoop\Entity\EntityEnforcerSubscriber;
use Zoop\Entity\Test\Assets\Product;
use Zoop\Entity\Test\Assets\Order;
use Zoop\Store\DataModel\Store;
use Zoop\User\DataModel\Partner\Admin;

class EntityEnforcerSubscriberTest extends AbstractTest
{
    public function testPrePersistStoreTrait()
    {
        $mocDocument = $this->getMock('Zoop\\Entity\\Test\\Assets\\Order');
        $mocObjectManager = $this->getMock('Doctrine\\Common\\Persistence\\ObjectManager');

        $enforcer = new SpyingEntityEnforcerSubscriber;
        $mocLifecycle = $this->getMock(
            'Doctrine\\ODM\\MongoDB\\Event\\LifecycleEventArgs',
            null,
            [
                $mocDocument,
                $mocObjectManager
            ]
        );

        $mocLifecycle->method('getDocument')
            ->willReturn($mocDocument);

        $enforcer->prePersist($mocLifecycle);

        $this->assertTrue($enforcer->getDoApplyStoreTrait());
    }

    public function testPrePersistStoresTrait()
    {
        $mocDocument = $this->getMock('Zoop\\Entity\\Test\\Assets\\Product');
        $mocObjectManager = $this->getMock('Doctrine\\Common\\Persistence\\ObjectManager');

        $enforcer = new SpyingEntityEnforcerSubscriber;
        $mocLifecycle = $this->getMock(
            'Doctrine\\ODM\\MongoDB\\Event\\LifecycleEventArgs',
            null,
            [
                $mocDocument,
                $mocObjectManager
            ]
        );

        $mocLifecycle->method('getDocument')
            ->willReturn($mocDocument);

        $enforcer->prePersist($mocLifecycle);

        $this->assertTrue($enforcer->getDoApplyEntitiesTrait());
    }

    public function testApplyEntityTraitActiveEntity()
    {
        $store = new Store;
        $store->setSlug('apple');

        $order = new Order;
        $order->setName('Test');
        $order->setPrice(100);

        $mocObjectManager = $this->getMock('Doctrine\\Common\\Persistence\\ObjectManager');
        $mocLifecycle = $this->getMock(
            'Doctrine\\ODM\\MongoDB\\Event\\LifecycleEventArgs',
            null,
            [
                $order,
                $mocObjectManager
            ]
        );
        $mocLifecycle->method('getDocument')
            ->willReturn($order);

        $map = [
            ['zoop.commerce.entity.active', $store]
        ];

        $mocServiceLocator = $this->getMock('Zend\\ServiceManager\\ServiceLocatorInterface');
        $mocServiceLocator->expects($this->any())
            ->method('get')
             ->will($this->returnValueMap($map));

        $enforcer = new EntityEnforcerSubscriber;
        $enforcer->setServiceLocator($mocServiceLocator);

        $enforcer->prePersist($mocLifecycle);

        $this->assertNotEmpty($order->getStore());
        $this->assertEquals('apple', $order->getStore());
    }

    public function testApplyStoreTraitActiveUser()
    {
        $order = new Order;
        $order->setName('Test');
        $order->setPrice(100);

        $user = new Admin;
        $user->setEntities(['apple', 'demo']);

        $mocObjectManager = $this->getMock('Doctrine\\Common\\Persistence\\ObjectManager');
        $mocLifecycle = $this->getMock(
            'Doctrine\\ODM\\MongoDB\\Event\\LifecycleEventArgs',
            null,
            [
                $order,
                $mocObjectManager
            ]
        );
        $mocLifecycle->method('getDocument')
            ->willReturn($order);

        $map = [
            ['zoop.commerce.entity.active', false],
            ['user', $user]
        ];

        $mocServiceLocator = $this->getMock('Zend\\ServiceManager\\ServiceLocatorInterface');
        $mocServiceLocator->expects($this->any())
            ->method('get')
             ->will($this->returnValueMap($map));

        $enforcer = new EntityEnforcerSubscriber;
        $enforcer->setServiceLocator($mocServiceLocator);

        $enforcer->prePersist($mocLifecycle);

        $this->assertNotEmpty($order->getStore());
        $this->assertEquals('apple', $order->getStore());
    }

    public function testApplyEntitiesTraitActiveStore()
    {
        $store = new Store;
        $store->setSlug('apple');

        $product = new Product;
        $product->setName('Test');

        $mocObjectManager = $this->getMock('Doctrine\\Common\\Persistence\\ObjectManager');
        $mocLifecycle = $this->getMock(
            'Doctrine\\ODM\\MongoDB\\Event\\LifecycleEventArgs',
            null,
            [
                $product,
                $mocObjectManager
            ]
        );
        $mocLifecycle->method('getDocument')
            ->willReturn($product);

        $map = [
            ['zoop.commerce.entity.active', $store]
        ];

        $mocServiceLocator = $this->getMock('Zend\\ServiceManager\\ServiceLocatorInterface');
        $mocServiceLocator->expects($this->any())
            ->method('get')
             ->will($this->returnValueMap($map));

        $enforcer = new EntityEnforcerSubscriber;
        $enforcer->setServiceLocator($mocServiceLocator);

        $enforcer->prePersist($mocLifecycle);

        $this->assertNotEmpty($product->getStores());
        $this->assertEquals(['apple'], $product->getStores());
    }

    public function testApplyEntitiesTraitActiveUser()
    {
        $stores = ['apple', 'demo'];

        $product = new Product;
        $product->setName('Test');

        $user = new Admin;
        $user->setEntities($stores);

        $mocObjectManager = $this->getMock('Doctrine\\Common\\Persistence\\ObjectManager');
        $mocLifecycle = $this->getMock(
            'Doctrine\\ODM\\MongoDB\\Event\\LifecycleEventArgs',
            null,
            [
                $product,
                $mocObjectManager
            ]
        );
        $mocLifecycle->method('getDocument')
            ->willReturn($product);

        $map = [
            ['zoop.commerce.entity.active', false],
            ['user', $user]
        ];

        $mocServiceLocator = $this->getMock('Zend\\ServiceManager\\ServiceLocatorInterface');
        $mocServiceLocator->expects($this->any())
            ->method('get')
             ->will($this->returnValueMap($map));

        $enforcer = new EntityEnforcerSubscriber;
        $enforcer->setServiceLocator($mocServiceLocator);

        $enforcer->prePersist($mocLifecycle);

        $this->assertNotEmpty($product->getStores());
        $this->assertEquals($stores, $product->getStores());
    }

    /**
     * @expectedException \Zoop\Entity\Exception\MissingEntityFilterException
     */
    public function testMissingStoreFilterException()
    {
        $stores = ['apple', 'demo'];

        $product = new Product;
        $product->setName('Test');

        $user = new Admin;
        $user->setEntities($stores);

        $mocObjectManager = $this->getMock('Doctrine\\Common\\Persistence\\ObjectManager');
        $mocLifecycle = $this->getMock(
            'Doctrine\\ODM\\MongoDB\\Event\\LifecycleEventArgs',
            null,
            [
                $product,
                $mocObjectManager
            ]
        );
        $mocLifecycle->method('getDocument')
            ->willReturn($product);

        $map = [
            ['zoop.commerce.entity.active', false],
            ['user', false]
        ];

        $mocServiceLocator = $this->getMock('Zend\\ServiceManager\\ServiceLocatorInterface');
        $mocServiceLocator->expects($this->any())
            ->method('get')
             ->will($this->returnValueMap($map));

        $enforcer = new EntityEnforcerSubscriber;
        $enforcer->setServiceLocator($mocServiceLocator);

        $enforcer->prePersist($mocLifecycle);
    }
}
