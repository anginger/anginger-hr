<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace anginger\modules\department\assets;

use anginger\assets\AppAsset;
use yii\web\AssetBundle;

class DepartmentAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $js = ['assets/module-department.js'];
    public $jsOptions = ['position' => \yii\web\View::POS_END]; // classic end-of-body: runs before deferred Alpine start
    public $depends = [AppAsset::class];
}
