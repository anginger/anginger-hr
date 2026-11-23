<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

// Console entry script, e.g.: php console.php migrate --interactive=0

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/config.php';

// strip web-only components so the console app boots cleanly
foreach (['request', 'response', 'user', 'session', 'urlManager', 'errorHandler'] as $component) {
    unset($config['components'][$component]);
}

$application = new yii\console\Application($config);
$exitCode = $application->run();
exit($exitCode);
