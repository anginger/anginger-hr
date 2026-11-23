<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

use anginger\modules\account\assets\AccountAsset;

/* @var $this \yii\web\View */
/* @var $items \anginger\models\User[] */
/* @var $employees \anginger\models\Employee[] */

$this->title = '帳號管理 - Anginger HR';
AccountAsset::register($this);
?>
<div x-data="accountPage()">
    <div class="page-head">
        <h1>帳號管理</h1>
        <button class="btn primary" @click="openCreate()">+ 新增帳號</button>
    </div>
    <div class="card">
        <table>
            <thead>
            <tr><th>員工</th><th>帳號</th><th>建立時間</th><th></th></tr>
            </thead>
            <tbody>
            <template x-for="u in items" :key="u.uuid">
                <tr>
                    <td x-text="u.display_name"></td>
                    <td x-text="u.username"></td>
                    <td x-text="ts(u.created_time)"></td>
                    <td>
                        <button class="btn" @click="openEdit(u)">編輯</button>
                        <button class="btn danger" @click="remove(u)">刪除</button>
                    </td>
                </tr>
            </template>
            <tr x-show="items.length === 0"><td colspan="4" class="empty">尚無資料</td></tr>
            </tbody>
        </table>
    </div>

    <div class="modal" x-show="mode !== 0" @click.self="mode = 0">
        <div class="card modal-card">
            <h2 x-text="mode === 1 ? '新增帳號' : '編輯帳號'"></h2>
            <p class="error" x-show="message" x-text="message"></p>
            <label>員工
                <select x-model="form.uuid" :disabled="mode === 2">
                    <template x-for="e in employees" :key="e.emp_id">
                        <option :value="e.emp_id" x-text="empLabel(e)"></option>
                    </template>
                </select>
            </label>
            <label>帳號<input x-model="form.username"></label>
            <label>密碼（留空表示不變）<input type="password" x-model="form.password"></label>
            <div class="row">
                <button class="btn" @click="mode = 0">取消</button>
                <button class="btn primary" @click="submit()" x-text="mode === 1 ? '送出' : '儲存'"></button>
            </div>
        </div>
    </div>
</div>
<script>
    window.__ITEMS__ = <?= json_encode($items) ?>;
    window.__EMPLOYEES__ = <?= json_encode($employees) ?>;
</script>
