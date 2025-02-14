<?php

return [
    'paths' => [
        'migrations' => 'db/migrations',
        'seeds' => 'db/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'development',
        'production' => [
            'adapter' => 'mysql',
            'host' => 'localhost',
            'name' => 'torneo_db_production',
            'user' => 'root',
            'pass' => '',
            'port' => 3306,
            'charset' => 'utf8mb4',
        ],
        'development' => [
            'adapter' => 'mysql',
            'host' => 'geo-db8-1',
            'name' => 'geo',
            'user' => 'root',
            'pass' => 'root',
            'port' => 3306,
            'charset' => 'utf8mb4',
        ],
        'testing' => [
            'adapter' => 'mysql',
            'host' => 'geo-db8-1',
            'name' => 'geo_test',
            'user' => 'root',
            'pass' => 'root',
            'port' => 3306,
            'charset' => 'utf8mb4',
        ]
    ],
    'version_order' => 'creation'
];
