<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

use anginger\assets\AppAsset;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */

AppAsset::register($this);
$path = Yii::$app->request->pathInfo;
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
<header class="ts-app-topbar">
    <div class="start">
        <div class="item is-text"><strong>Anginger HR</strong></div>
    </div>
    <div class="center">
        <nav class="ts-menu">
            <a class="item<?= $path === '' ? ' is-active' : '' ?>" href="/">總覽</a>
            <a class="item<?= $path === 'employee' ? ' is-active' : '' ?>" href="/employee">員工</a>
            <a class="item<?= $path === 'department' ? ' is-active' : '' ?>" href="/department">部門</a>
            <a class="item<?= $path === 'account' ? ' is-active' : '' ?>" href="/account">帳號</a>
        </nav>
    </div>
    <div class="end">
        <?php if (!Yii::$app->user->isGuest): ?>
            <div class="item is-text"><?= Html::encode(Yii::$app->user->identity->username) ?></div>
            <a class="item" href="/site/logout">登出</a>
        <?php endif; ?>
    </div>
</header>
<main class="ts-container app-main">
    <?= $content ?>
</main>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
