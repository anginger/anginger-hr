<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

return [
    'id' => 'anginger-hr',
    'basePath' => __DIR__,
    'controllerNamespace' => 'anginger\\controllers',
    'aliases' => [
        '@anginger' => __DIR__,
    ],
    'components' => [
        'db' => [
            'class' => 'yii\\db\\Connection',
            // To use MySQL instead of SQLite, change the DSN to e.g.
            // 'mysql:host=127.0.0.1;dbname=anginger_hr' and add username/password.
            'dsn' => 'sqlite:@anginger/database.sqlite',
        ],
        'request' => [
            // required for the session cookie
            'cookieValidationKey' => 'anginger-hr-dev-key-change-me-in-production',
            // the Alpine.js frontend posts URLSearchParams via fetch(); also accept JSON bodies
            'enableCsrfValidation' => false,
            'parsers' => [
                'application/json' => 'yii\\web\\JsonParser',
            ],
        ],
        // session-based authentication; pages are server-rendered HTML,
        // API controllers return JSON explicitly via asJson()
        'user' => [
            'identityClass' => 'anginger\\models\\User',
            'enableSession' => true,
            'enableAutoLogin' => false,
            'loginUrl' => null, // API returns 401/403 JSON instead of redirecting
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                // pages
                'login' => 'site/login',
                'logout' => 'site/logout',
                // every business domain is a module with its own page + JSON API
                '<module:employee|department|account>' => '<module>/page/index',
                // JSON API consumed by the Alpine.js frontend (fetch)
                'api/authentic/<action:\w+>' => 'authentic/<action>',
                'api/<module:employee|department|account>' => '<module>/api/index',
                'api/<module:employee|department|account>/<action:\w+>' => '<module>/api/<action>',
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
    ],
    // application-level modules (NOT a component): each business domain
    // owns its page route and its /api/* JSON API
    'modules' => [
        'employee' => 'anginger\\modules\\employee\\Module',
        'department' => 'anginger\\modules\\department\\Module',
        'account' => 'anginger\\modules\\account\\Module',
    ],
];
