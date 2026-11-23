<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

use anginger\modules\department\assets\DepartmentAsset;

/* @var $this \yii\web\View */
/* @var $items \anginger\models\Department[] */
/* @var $employees \anginger\models\Employee[] */

$this->title = '部門管理 - Anginger HR';
DepartmentAsset::register($this);
?>
<div x-data="departmentPage()">
    <div class="page-head">
        <h1>部門管理</h1>
        <button class="ts-button" @click="openCreate()">+ 新增部門</button>
    </div>
    <div class="ts-box">
        <table class="ts-table">
            <thead>
            <tr><th>部門代號</th><th>部門名稱</th><th>主管</th><th></th></tr>
            </thead>
            <tbody>
            <template x-for="d in items" :key="d.dep_id">
                <tr>
                    <td x-text="d.dep_id"></td>
                    <td x-text="d.dep_name"></td>
                    <td x-text="managerName(d.manager_emp_id)"></td>
                    <td>
                        <button class="ts-button is-small is-outlined" @click="openEdit(d)">編輯</button>
                        <button class="ts-button is-small is-negative" @click="remove(d)">刪除</button>
                    </td>
                </tr>
            </template>
            <tr x-show="items.length === 0"><td colspan="4" class="empty">尚無資料</td></tr>
            </tbody>
        </table>
    </div>

    <dialog class="ts-modal" :open="mode !== 0" @click.self="mode = 0">
        <form class="content" @submit.prevent="submit()">
            <h2 x-text="mode === 1 ? '新增部門' : '編輯部門'"></h2>
            <p class="error" aria-live="polite" x-show="message" x-text="message"></p>
            <label class="app-field">部門名稱<div class="ts-input"><input name="dep_name" autocomplete="off" x-model="form.dep_name"></div></label>
            <label class="app-field">主管
                <div class="ts-input">
                    <select name="manager_emp_id" x-model="form.manager_emp_id">
                        <template x-for="e in employees" :key="e.emp_id">
                            <option :value="e.emp_id" x-text="empLabel(e)"></option>
                        </template>
                    </select>
                </div>
            </label>
            <div class="ts-buttons is-end-aligned">
                <button class="ts-button is-outlined" type="button" @click="mode = 0">取消</button>
                <button class="ts-button" type="submit" x-text="mode === 1 ? '送出' : '儲存'"></button>
            </div>
        </form>
    </dialog>
</div>
<script>
    window.__ITEMS__ = <?= json_encode($items) ?>;
    window.__EMPLOYEES__ = <?= json_encode($employees) ?>;
</script>
