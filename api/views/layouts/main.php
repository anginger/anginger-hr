<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

use anginger\assets\AppAsset;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $content string */

AppAsset::register($this);
$this->beginPage();
?><!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Html::encode($this->title ?: 'Anginger HR') ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<header class="topbar">
    <div class="brand">Anginger HR</div>
    <nav class="nav">
        <a href="<?= Url::to(['/site/index']) ?>">總覽</a>
        <a href="<?= Url::to(['/employee']) ?>">員工</a>
        <a href="<?= Url::to(['/department']) ?>">部門</a>
        <a href="<?= Url::to(['/account']) ?>">帳號</a>
    </nav>
    <div class="user">
        <?php if (!Yii::$app->user->isGuest): ?>
            <span><?= Html::encode(Yii::$app->user->identity->username) ?></span>
            <a href="<?= Url::to(['/site/logout']) ?>">登出</a>
        <?php endif; ?>
    </div>
</header>
<main class="container">
    <?= $content ?>
</main>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
