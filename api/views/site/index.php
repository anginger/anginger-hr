<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $stats array */
/* @var $histories \anginger\models\History[] */
$this->title = '總覽 - Anginger HR';
?>
<div class="ts-box" style="margin-bottom:1rem">
    <h1>總覽</h1>
    <p>歡迎，<?= Html::encode(Yii::$app->user->identity->username) ?>。</p>
    <p class="muted">Anginger HR 是一款專為臺灣中小企業設計的人力資源管理系統。</p>
</div>
<div class="stats-grid">
    <div class="ts-box"><div class="ts-statistic"><div class="value"><?= (int)$stats['employees_count'] ?></div><div class="description">員工總數</div></div></div>
    <div class="ts-box"><div class="ts-statistic"><div class="value"><?= (int)$stats['employees_city_count'] ?></div><div class="description">員工所在縣市數</div></div></div>
    <div class="ts-box"><div class="ts-statistic"><div class="value"><?= (int)$stats['employees_salaries_avg'] ?></div><div class="description">平均月薪</div></div></div>
    <div class="ts-box"><div class="ts-statistic"><div class="value"><?= (int)$stats['departments_count'] ?></div><div class="description">部門總數</div></div></div>
    <div class="ts-box"><div class="ts-statistic"><div class="value"><?= (int)$stats['departments_salaries_avg'] ?></div><div class="description">部門平均薪資總額</div></div></div>
    <div class="ts-box"><div class="ts-statistic"><div class="value"><?= (int)$stats['departments_employees_count_avg'] ?></div><div class="description">部門平均人數</div></div></div>
</div>
<div class="ts-box" style="margin-bottom:1rem">
    <div class="page-head"><h2>各部門人數</h2></div>
    <table class="ts-table">
        <thead><tr><th>部門</th><th>人數</th></tr></thead>
        <tbody>
        <?php foreach ($stats['departments_employees_counts'] as [$depName, $count]): ?>
            <tr><td><?= Html::encode($depName) ?></td><td><?= (int)$count ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<div class="ts-box">
    <div class="page-head"><h2>操作紀錄</h2></div>
    <table class="ts-table">
        <thead><tr><th>時間</th><th>使用者</th><th>方法</th><th>資源</th></tr></thead>
        <tbody>
        <?php foreach ($histories as $h): ?>
            <tr>
                <td><?= Html::encode(date('Y-m-d H:i:s', (int)$h->created_time)) ?></td>
                <td><?= Html::encode($h->user) ?></td>
                <td><span class="ts-chip"><?= Html::encode($h->method) ?></span></td>
                <td><?= Html::encode($h->resource) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($histories)): ?>
            <tr><td colspan="4" class="empty">尚無紀錄</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
