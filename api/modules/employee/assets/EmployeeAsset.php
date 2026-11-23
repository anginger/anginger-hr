<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace anginger\modules\employee\assets;

use anginger\assets\AppAsset;
use yii\web\AssetBundle;

class EmployeeAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $js = ['assets/module-employee.js'];
    public $depends = [AppAsset::class];
}
