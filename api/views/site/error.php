<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $exception \Throwable */
$this->title = '錯誤 - Anginger HR';
?>
<div class="card login-card">
    <h1><?= Html::encode($this->title) ?></h1>
    <p class="error"><?= Html::encode($exception->getMessage()) ?></p>
    <p><a href="/">回到首頁</a></p>
</div>
