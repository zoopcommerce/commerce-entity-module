<?php

return array(
    'zoop' => [
        'api' => [
            'endpoints' => [
                'customers',
                'partners',
                'stores',
            ],
            'filters' => [
                'stores' => [
                    'route' => '/stores/:store',
                    'constraints' => [
                        'store' => '[a-zA-Z0-9_\-]+'
                    ]
                ]
            ]
        ],
        'domain' => [
            'storefront' => 'zoopcommerce.com',
        ],
        'shard' => [
            'rest' => [
                'rest' => [
                    'customers' => [
                        'manifest' => 'commerce',
                        'class' => 'Zoop\Entity\DataModel\Customer',
                        'property' => 'id',
                        'listeners' => [
                            'create' => [
                                'zoop.shardmodule.listener.unserialize',
                                'zoop.api.listener.cors',
                                'zoop.shardmodule.listener.create',
                                'zoop.shardmodule.listener.flush',
                                'zoop.shardmodule.listener.location',
                                'zoop.shardmodule.listener.prepareviewmodel'
                            ],
                            'delete' => [
                                'zoop.shardmodule.listener.delete',
                                'zoop.api.listener.cors',
                                'zoop.shardmodule.listener.flush',
                                'zoop.shardmodule.listener.prepareviewmodel'
                            ],
                            'deleteList' => [],
                            'get' => [
                                'zoop.shardmodule.listener.get',
                                'zoop.api.listener.cors',
                                'zoop.shardmodule.listener.serialize',
                                'zoop.shardmodule.listener.prepareviewmodel'
                            ],
                            'getList' => [
                                'zoop.shardmodule.listener.getlist',
                                'zoop.api.listener.cors',
                                'zoop.shardmodule.listener.serialize',
                                'zoop.shardmodule.listener.prepareviewmodel'
                            ],
                            'patch' => [
                                'zoop.shardmodule.listener.unserialize',
                                'zoop.api.listener.cors',
                                'zoop.shardmodule.listener.idchange',
                                'zoop.shardmodule.listener.patch',
                                'zoop.shardmodule.listener.flush',
                                'zoop.shardmodule.listener.prepareviewmodel'
                            ],
                            'patchList' => [],
                            'update' => [
                                'zoop.shardmodule.listener.unserialize',
                                'zoop.api.listener.cors',
                                'zoop.shardmodule.listener.idchange',
                                'zoop.shardmodule.listener.update',
                                'zoop.shardmodule.listener.flush',
                                'zoop.shardmodule.listener.prepareviewmodel'
                            ],
                            'replaceList' => [],
                            'options' => [
                                'zoop.api.listener.options',
                                'zoop.shardmodule.listener.prepareviewmodel'
                            ],
                        ],
                    ],
                    'partners' => [
                        'manifest' => 'commerce',
                        'class' => 'Zoop\Entity\DataModel\Partner',
                        'property' => 'id',
//                        'listeners' => [
//                            'create' => [
//                                'zoop.shardmodule.listener.unserialize',
//                                'zoop.shardmodule.listener.create',
//                                'zoop.shardmodule.listener.flush',
//                                'zoop.shardmodule.listener.location',
//                                'zoop.shardmodule.listener.prepareviewmodel'
//                            ],
//                            'delete' => [
//                                'zoop.shardmodule.listener.delete',
//                                'zoop.shardmodule.listener.flush',
//                                'zoop.shardmodule.listener.prepareviewmodel'
//                            ],
//                            'deleteList' => [],
//                            'get' => [
//                                'zoop.shardmodule.listener.get',
//                                'zoop.shardmodule.listener.serialize',
//                                'zoop.shardmodule.listener.prepareviewmodel'
//                            ],
//                            'getList' => [
//                                'zoop.shardmodule.listener.getlist',
//                                'zoop.shardmodule.listener.serialize',
//                                'zoop.shardmodule.listener.prepareviewmodel'
//                            ],
//                            'patch' => [
//                                'zoop.shardmodule.listener.unserialize',
//                                'zoop.shardmodule.listener.idchange',
//                                'zoop.shardmodule.listener.patch',
//                                'zoop.shardmodule.listener.flush',
//                                'zoop.shardmodule.listener.prepareviewmodel'
//                            ],
//                            'patchList' => [],
//                            'update' => [
//                                'zoop.shardmodule.listener.unserialize',
//                                'zoop.shardmodule.listener.idchange',
//                                'zoop.shardmodule.listener.update',
//                                'zoop.shardmodule.listener.flush',
//                                'zoop.shardmodule.listener.prepareviewmodel'
//                            ],
//                            'replaceList' => [],
//                            'options' => [],
//                        ],
                    ],
                    'stores' => [
                        'manifest' => 'commerce',
                        'class' => 'Zoop\Store\DataModel\Store',
                        'property' => 'slug',
                        'listeners' => [
                            'create' => [
                                'zoop.shardmodule.listener.unserialize',
                                'zoop.api.listener.cors',
                                'zoop.shardmodule.listener.create',
                                'zoop.commerce.entity.listener.updateusers',
                                'zoop.shardmodule.listener.flush',
                                'zoop.shardmodule.listener.location',
                                'zoop.shardmodule.listener.prepareviewmodel'
                            ],
                            'delete' => [
                                'zoop.shardmodule.listener.delete',
                                'zoop.api.listener.cors',
                                'zoop.shardmodule.listener.flush',
                                'zoop.shardmodule.listener.prepareviewmodel'
                            ],
                            'deleteList' => [],
                            'get' => [
                                'zoop.shardmodule.listener.get',
                                'zoop.api.listener.cors',
                                'zoop.shardmodule.listener.serialize',
                                'zoop.shardmodule.listener.prepareviewmodel'
                            ],
                            'getList' => [
                                'zoop.shardmodule.listener.getlist',
                                'zoop.api.listener.cors',
                                'zoop.shardmodule.listener.serialize',
                                'zoop.shardmodule.listener.prepareviewmodel'
                            ],
                            'patch' => [
                                'zoop.shardmodule.listener.unserialize',
                                'zoop.api.listener.cors',
                                'zoop.shardmodule.listener.idchange',
                                'zoop.shardmodule.listener.patch',
                                'zoop.shardmodule.listener.flush',
                                'zoop.shardmodule.listener.prepareviewmodel'
                            ],
                            'patchList' => [],
                            'update' => [
                                'zoop.shardmodule.listener.unserialize',
                                'zoop.api.listener.cors',
                                'zoop.shardmodule.listener.idchange',
                                'zoop.shardmodule.listener.update',
                                'zoop.shardmodule.listener.flush',
                                'zoop.shardmodule.listener.prepareviewmodel'
                            ],
                            'replaceList' => [],
                            'options' => [
                                'zoop.api.listener.options',
                                'zoop.shardmodule.listener.prepareviewmodel'
                            ],
                        ],
                    ],
                ]
            ],
        ],
    ],
    'router' => [
        'prototypes' => [
            'zoop/commerce/entity' => [
                'type' => 'Hostname',
                'options' => [
                    'route' => ':entity.zoopcommerce.com'
                ],
            ],
        ],
    ],
    'service_manager' => [
        'invokables' => [
            'zoop.commerce.entity.dispatchlistener' => 'Zoop\Entity\DispatchListener',
            'zoop.commerce.entity.filterlistener' => 'Zoop\Entity\EntityFilterListener',
            'zoop.commerce.entity.enforcersubscriber' => 'Zoop\Entity\EntityEnforcerSubscriber',
            'zoop.commerce.entity.listener.updateusers' => 'Zoop\Entity\Controller\UpdateUsersListener',
        ],
        'factories' => [
        ],
        'abstract_factories' => [
            'Zoop\Entity\Service\ActiveEntityFactory' //zoop.commerce.entity.active
        ]
    ],
);
