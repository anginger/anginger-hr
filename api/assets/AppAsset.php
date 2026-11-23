<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace anginger\assets;

use yii\web\AssetBundle;

/**
 * Global assets: Alpine.js (localized, no CDN dependency) + shared CSS.
 * Every module asset bundle depends on this one.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = ['assets/app.css'];
    public $js = ['assets/alpine.min.js'];
}
