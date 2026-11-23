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
            'cookieValidationKey' => 'anginger-hr-change-me-in-production',
            // The SPA authenticates with a session cookie and does not send
            // CSRF tokens (same behaviour as the original API).
            'enableCsrfValidation' => false,
            'parsers' => [
                'application/json' => 'yii\\web\\JsonParser',
            ],
        ],
        'session' => [
            'name' => 'ANGINGERHR',
            'cookieParams' => [
                'httponly' => true,
                'samesite' => 'Lax',
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'GET authentic/session' => 'authentic/get-session',
                'POST authentic/session' => 'authentic/post-session',
                'DELETE authentic/session' => 'authentic/delete-session',

                'GET manage' => 'manage/index',
                'GET manage/employees' => 'manage/all-employees',
                'GET manage/employee' => 'manage/get-employee',
                'POST manage/employee' => 'manage/post-employee',
                'PUT manage/employee' => 'manage/put-employee',
                'DELETE manage/employee' => 'manage/delete-employee',
                'GET manage/departments' => 'manage/all-departments',
                'GET manage/department/employees' => 'manage/department-employees',
                'GET manage/department' => 'manage/get-department',
                'POST manage/department' => 'manage/post-department',
                'PUT manage/department' => 'manage/put-department',
                'PATCH manage/department' => 'manage/patch-department',
                'DELETE manage/department' => 'manage/delete-department',

                'GET setting' => 'setting/index',
                'GET setting/users' => 'setting/all-users',
                'GET setting/user' => 'setting/get-user',
                'POST setting/user' => 'setting/post-user',
                'PUT setting/user' => 'setting/put-user',
                'DELETE setting/user' => 'setting/delete-user',
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
    ],
];
