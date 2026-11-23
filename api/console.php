<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

return [
    'id' => 'anginger-hr-console',
    'basePath' => __DIR__,
    'controllerNamespace' => 'anginger\\commands',
    'aliases' => [
        '@anginger' => __DIR__,
    ],
    'components' => [
        'db' => [
            'class' => 'yii\\db\\Connection',
            'dsn' => 'sqlite:@anginger/database.sqlite',
        ],
    ],
    'controllerMap' => [
        'migrate' => [
            'class' => 'yii\\console\\controllers\\MigrateController',
            'migrationPath' => '@anginger/migrations',
        ],
    ],
];
