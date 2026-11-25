<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

$config = [
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
            'dsn' => getenv('DB_DSN') ?: ('sqlite:' . (getenv('SQLITE_PATH') ?: '@anginger/database.sqlite')),
            // SQLite: WAL + busy timeout + FK enforcement on every connection
            'on afterOpen' => function ($event) {
                if ($event->sender->driverName === 'sqlite') {
                    $event->sender->createCommand('PRAGMA journal_mode=WAL; PRAGMA busy_timeout=5000; PRAGMA foreign_keys=ON;')->execute();
                }
            },
        ],
        'request' => [
            // required for the session cookie
            'cookieValidationKey' => (function () {
                $key = getenv('COOKIE_VALIDATION_KEY');
                if (is_string($key) && $key !== '') {
                    return $key;
                }
                if (getenv('YII_ENV') === 'prod') {
                    throw new \yii\base\InvalidConfigException('COOKIE_VALIDATION_KEY is required when YII_ENV=prod');
                }
                return 'anginger-dev-key-change-me-in-production'; // dev only
            })(),
            // the Alpine.js frontend posts URLSearchParams via fetch(); also accept JSON bodies
            'enableCsrfValidation' => true,
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
        'session' => [
            'cookieParams' => [
                'httpOnly' => true,
                'sameSite' => 'Lax',
                'secure' => filter_var(getenv('SESSION_SECURE') ?: 'false', FILTER_VALIDATE_BOOLEAN),
            ],
        ],
        'cache' => ['class' => 'yii\\caching\\FileCache'],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                // pages
                'login' => 'site/login',
                'logout' => 'site/logout',
                'health' => 'site/health',
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
    'params' => [
        // LDAP / Active Directory login. Every value can be set via an
        // environment variable (LDAP_*) or overridden in config.local.php
        // (gitignored) — copy config.local.sample.php to get started.
        //
        // When enabled: built-in registration is disabled, LDAP users are
        // provisioned into `users` on first login, and built-in (local)
        // accounts keep working with their local password.
        'ldap' => [
            'enabled' => filter_var(getenv('LDAP_ENABLED') ?: 'false', FILTER_VALIDATE_BOOLEAN),
            'host' => getenv('LDAP_HOST') ?: '127.0.0.1',
            'port' => (int)(getenv('LDAP_PORT') ?: 389),          // 636 for LDAPS
            'base_dn' => getenv('LDAP_BASE_DN') ?: '',            // e.g. DC=company,DC=com
            'bind_dn' => getenv('LDAP_BIND_DN') ?: '',            // service account (optional)
            'bind_password' => getenv('LDAP_BIND_PASSWORD') ?: '',
            // strategy 1 (service account search): user_filter is sprintf'd with
            // the escaped username — AD: (sAMAccountName=%s), OpenLDAP: (uid=%s)
            'user_filter' => getenv('LDAP_USER_FILTER') ?: '(uid=%s)',
            // strategy 2 (direct bind, no service account): DN pattern, %s = escaped username
            'user_dn_pattern' => getenv('LDAP_USER_DN_PATTERN') ?: '',
            'username_attribute' => getenv('LDAP_USERNAME_ATTRIBUTE') ?: 'uid',   // AD: sAMAccountName
            'display_name_attribute' => getenv('LDAP_DISPLAY_NAME_ATTRIBUTE') ?: 'cn',
            'email_attribute' => getenv('LDAP_EMAIL_ATTRIBUTE') ?: 'mail',
            'start_tls' => filter_var(getenv('LDAP_STARTTLS') ?: 'false', FILTER_VALIDATE_BOOLEAN),
            'timeout' => (int)(getenv('LDAP_TIMEOUT') ?: 5),
        ],
    ],
];

// local overrides (secrets) — copy config.local.sample.php to config.local.php
$localConfig = __DIR__ . '/config.local.php';
if (is_file($localConfig)) {
    $config = array_replace_recursive($config, require $localConfig);
}

return $config;
