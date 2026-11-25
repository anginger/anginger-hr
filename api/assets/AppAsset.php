<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace anginger\assets;

use yii\web\AssetBundle;

/**
 * Global assets: TocasUI (vendored, no CDN dependency) + project overrides,
 * plus Alpine.js (vendored). Every module asset bundle depends on this one.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'assets/vendor/tocas.min.css',
        'assets/app.css',
    ];
    public $js = ['assets/app-ui.js', 'assets/alpine.min.js']; // app-ui first: registers alpine:init hooks before Alpine auto-starts
    // Alpine 3.14 starts on a microtask as soon as its script runs; defer makes
    // it execute after classic end-of-body module scripts, so their
    // 'alpine:init' listeners are registered before Alpine dispatches the event.
    public $jsOptions = ['defer' => true];
}
