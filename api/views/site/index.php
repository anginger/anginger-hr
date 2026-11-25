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
<div class="ts-box welcome-card" style="margin-bottom:1rem">
    <h1>總覽</h1>
    <p>歡迎，<?= Html::encode(Yii::$app->user->identity->username) ?>。</p>
    <p class="muted">Anginger HR 是一款專為臺灣中小企業設計的人力資源管理系統。</p>
</div>
<div class="stats-grid">
    <div class="ts-box stat-card">
        <span class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>
        <div><div class="stat-value"><?= (int)$stats['employees_count'] ?></div><div class="stat-label">員工總數</div></div>
    </div>
    <div class="ts-box stat-card">
        <span class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-5.1-7-11a7 7 0 1 1 14 0c0 5.9-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg></span>
        <div><div class="stat-value"><?= (int)$stats['employees_city_count'] ?></div><div class="stat-label">員工所在縣市數</div></div>
    </div>
    <div class="ts-box stat-card">
        <span class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/><path d="M6 12h.01M18 12h.01"/></svg></span>
        <div><div class="stat-value"><?= (int)$stats['employees_salaries_avg'] ?></div><div class="stat-label">平均月薪</div></div>
    </div>
    <div class="ts-box stat-card">
        <span class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="3" width="16" height="18" rx="1.5"/><path d="M9 7h1M14 7h1M9 11h1M14 11h1M9 15h1M14 15h1"/></svg></span>
        <div><div class="stat-value"><?= (int)$stats['departments_count'] ?></div><div class="stat-label">部門總數</div></div>
    </div>
    <div class="ts-box stat-card">
        <span class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 15l4-5 3 3 5-7"/></svg></span>
        <div><div class="stat-value"><?= (int)$stats['departments_salaries_avg'] ?></div><div class="stat-label">部門平均薪資總額</div></div>
    </div>
    <div class="ts-box stat-card">
        <span class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 4.6a3.5 3.5 0 0 1 0 6.8"/><path d="M17.5 14.4a6.5 6.5 0 0 1 4 5.6"/></svg></span>
        <div><div class="stat-value"><?= (int)$stats['departments_employees_count_avg'] ?></div><div class="stat-label">部門平均人數</div></div>
    </div>
</div>
<div class="ts-box" style="margin-bottom:1rem">
    <div class="page-head">
        <div class="ts-input app-search"><input type="search" placeholder="搜尋…" x-model="q" aria-label="搜尋列表"></div><h2>各部門人數</h2></div>
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
                <td><span class="ts-chip m-<?= strtolower(Html::encode($h->method)) ?>"><?= Html::encode($h->method) ?></span></td>
                <td><?= Html::encode($h->resource) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($histories)): ?>
            <tr><td colspan="4" class="empty">尚無紀錄</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
