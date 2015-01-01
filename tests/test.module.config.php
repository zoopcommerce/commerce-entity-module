<?php

$mongoConnectionString = 'mongodb://localhost:27017';
$mongoZoopDatabase = 'zoop_test';
$mysqlZoopDatabase = 'zoop_test';

return [
    'router' => [
        'prototypes' => [
            'zoop/commerce/api' => [
                'type' => 'Hostname',
                'options' => [
                    'route' => 'api.zoopcommerce.local'
                ],
            ],
            'zoop/commerce/entity' => [
                'type' => 'Hostname',
                'options' => [
                    'route' => ':entity.zoopcommerce.local'
                ],
            ],
        ],
    ],
    'doctrine' => [
        'odm' => [
            'connection' => [
                'commerce' => [
                    'dbname' => $mongoZoopDatabase,
                    'connectionString' => $mongoConnectionString,
                ],
            ],
            'configuration' => [
                'commerce' => [
                    'metadata_cache' => 'doctrine.cache.array',
                    'default_db' => $mongoZoopDatabase,
                    'generate_proxies' => true,
                    'proxy_dir' => __DIR__ . '/../data/proxies',
                    'proxy_namespace' => 'proxies',
                    'generate_hydrators' => true,
                    'hydrator_dir' => __DIR__ . '/../data/hydrators',
                    'hydrator_namespace' => 'hydrators',
                ]
            ],
        ],
    ],
    'zoop' => [
        'db' => [
            'host' => 'localhost',
            'database' => $mysqlZoopDatabase,
            'username' => 'zoop',
            'password' => 'yourtown1',
            'port' => 3306,
        ],
        'cache' => [
            'handler' => 'mongodb',
            'mongodb' => [
                'connectionString' => $mongoConnectionString,
                'options' => [
                    'database' => $mongoZoopDatabase,
                    'collection' => 'Cache',
                ]
            ],
        ],
        'sendgrid' => [
            'username' => '',
            'password' => ''
        ],
        'session' => [
            'ttl' => (60 * 60 * 3), //3 hours
            'handler' => 'mongodb',
            'mongodb' => [
                'connectionString' => $mongoConnectionString,
                'options' => [
                    'database' => $mongoZoopDatabase,
                    'collection' => 'Session',
                    'saveOptions' => [
                        'w' => 1
                    ]
                ]
            ]
        ],
        'shard' => [
            'manifest' => [
                'noauth' => [
                    'models' => [
                        'Zoop\DataModel' => __DIR__ .
                            '/../vendor/zoopcommerce/commerce-public-data-models-module/src/Zoop',
                    ]
                ]
            ],
        ]
    ]
];
