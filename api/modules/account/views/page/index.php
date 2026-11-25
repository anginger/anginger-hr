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
<div x-data="accountPage()" x-init="refresh()">
    <div class="page-head">
        <div class="ts-input app-search"><input type="search" placeholder="搜尋…" x-model="q" aria-label="搜尋列表"></div>
        <h1>帳號管理</h1>
        <button class="ts-button" @click="openCreate()" :disabled="ldap" :title="ldap ? 'LDAP/AD 已啟用，帳號由目錄同步，無法手動新增' : ''">+ 新增帳號</button>
    </div>
    <p class="muted" x-show="ldap">LDAP/AD 登入已啟用：內建註冊已停用，新帳號會在使用者首次登入時自動同步；內建帳號仍可使用本機密碼登入。</p>
    <div class="ts-box is-flush">
        <table class="ts-table">
            <thead>
            <tr><th>員工</th><th>帳號</th><th>來源</th><th>狀態</th><th>建立時間</th><th></th></tr>
            </thead>
            <tbody>
            <template x-for="u in filtered" :key="u.uuid">
                <tr :class="{archived: u.archived}">
                    <td><span class="cell-user"><span class="avatar" x-text="(u.display_name || u.username || '?').charAt(0).toUpperCase()"></span><span x-text="u.display_name"></span></span></td>
                    <td x-text="u.username"></td>
                    <td><span class="ts-chip" :class="u.auth_source === 'ldap' ? 'm-ldap' : 'm-local'" x-text="u.auth_source === 'ldap' ? 'LDAP' : '本地'"></span></td>
                    <td><span class="ts-chip" :class="u.archived ? 'm-archived' : 'm-ok'" x-text="u.archived ? '已封存' : '正常'"></span></td>
                    <td x-text="ts(u.created_time)"></td>
                    <td>
                        <button class="ts-button is-small is-outlined" @click="openEdit(u)">編輯</button>
                        <button class="ts-button is-small is-outlined" :class="u.archived ? 'is-positive' : 'is-warning'" @click="toggleArchive(u)" x-text="u.archived ? '解除封存' : '封存'"></button>
                        <button class="ts-button is-small is-negative is-outlined" @click="remove(u)">刪除</button>
                    </td>
                </tr>
            </template>
            <tr x-show="filtered().length === 0"><td colspan="6" class="empty">尚無資料</td></tr>
            </tbody>
        </table>
    </div>

    <dialog class="ts-modal" x-modal="mode !== 0" @modal-close="mode = 0" @click.self="mode = 0">
        <form class="content" @submit.prevent="submit()">
            <h2 x-text="mode === 1 ? '新增帳號' : '編輯帳號'"></h2>
            <p class="error" aria-live="polite" x-show="message" x-text="message"></p>
            <label class="app-field">員工
                <div class="ts-input">
                    <select name="uuid" x-model="form.uuid" :disabled="mode === 2">
                        <template x-for="e in employees" :key="e.emp_id">
                            <option :value="e.emp_id" x-text="empLabel(e)"></option>
                        </template>
                    </select>
                </div>
            </label>
            <label class="app-field">帳號<div class="ts-input"><input name="username" autocomplete="off" spellcheck="false" x-model="form.username"></div></label>
            <label class="app-field">密碼（留空表示不變）<div class="ts-input"><input type="password" name="password" autocomplete="new-password" x-model="form.password"></div></label>
            <div class="ts-buttons is-end-aligned">
                <button class="ts-button is-outlined" type="button" @click="mode = 0">取消</button>
                <button class="ts-button" type="submit" x-text="mode === 1 ? '送出' : '儲存'"></button>
            </div>
        </form>
    </dialog>
</div>
<script>
    window.__ITEMS__ = <?= \yii\helpers\Json::htmlEncode(\yii\helpers\ArrayHelper::toArray($items)) ?>;
    window.__EMPLOYEES__ = <?= \yii\helpers\Json::htmlEncode(\yii\helpers\ArrayHelper::toArray($employees)) ?>;
</script>
