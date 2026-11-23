<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

use anginger\modules\employee\assets\EmployeeAsset;

/* @var $this \yii\web\View */
/* @var $items \anginger\models\Employee[] */
/* @var $departments \anginger\models\Department[] */

$this->title = '員工管理 - Anginger HR';
EmployeeAsset::register($this);
?>
<div x-data="employeePage()">
    <div class="page-head">
        <h1>員工管理</h1>
        <button class="ts-button" @click="openCreate()">+ 新增員工</button>
    </div>
    <div class="ts-box is-flush">
        <table class="ts-table">
            <thead>
            <tr><th>姓名</th><th>部門</th><th>職稱</th><th>縣市</th><th>電話</th><th>月薪</th><th>年假</th><th></th></tr>
            </thead>
            <tbody>
            <template x-for="e in items" :key="e.emp_id">
                <tr>
                    <td x-text="e.emp_name"></td>
                    <td x-text="depName(e.dep_id)"></td>
                    <td x-text="e.job_title"></td>
                    <td x-text="e.city"></td>
                    <td x-text="e.phone"></td>
                    <td x-text="e.month_salary"></td>
                    <td x-text="e.annual_leave"></td>
                    <td>
                        <button class="ts-button is-small is-outlined" @click="openEdit(e)">編輯</button>
                        <button class="ts-button is-small is-negative is-outlined" @click="remove(e)">刪除</button>
                    </td>
                </tr>
            </template>
            <tr x-show="items.length === 0"><td colspan="8" class="empty">尚無資料</td></tr>
            </tbody>
        </table>
    </div>

    <dialog class="ts-modal" :open="mode !== 0" @click.self="mode = 0">
        <form class="content" @submit.prevent="submit()">
            <h2 x-text="mode === 1 ? '新增員工' : '編輯員工'"></h2>
            <p class="error" aria-live="polite" x-show="message" x-text="message"></p>
            <label class="app-field">姓名<div class="ts-input"><input name="emp_name" autocomplete="off" x-model="form.emp_name"></div></label>
            <label class="app-field">職稱<div class="ts-input"><input name="job_title" autocomplete="off" x-model="form.job_title"></div></label>
            <label class="app-field">部門
                <div class="ts-input">
                    <select name="dep_id" x-model="form.dep_id">
                        <template x-for="d in departments" :key="d.dep_id">
                            <option :value="d.dep_id" x-text="d.dep_name"></option>
                        </template>
                    </select>
                </div>
            </label>
            <label class="app-field">縣市<div class="ts-input"><input name="city" autocomplete="off" x-model="form.city"></div></label>
            <label class="app-field">地址<div class="ts-input"><input name="address" autocomplete="off" x-model="form.address"></div></label>
            <label class="app-field">電話<div class="ts-input"><input type="tel" name="phone" autocomplete="off" x-model="form.phone"></div></label>
            <label class="app-field">郵遞區號<div class="ts-input"><input name="zip_code" autocomplete="off" x-model="form.zip_code"></div></label>
            <label class="app-field">月薪<div class="ts-input"><input type="number" name="month_salary" autocomplete="off" x-model="form.month_salary"></div></label>
            <label class="app-field">年假天數<div class="ts-input"><input type="number" name="annual_leave" autocomplete="off" x-model="form.annual_leave"></div></label>
            <div class="ts-buttons is-end-aligned">
                <button class="ts-button is-outlined" type="button" @click="mode = 0">取消</button>
                <button class="ts-button" type="submit" x-text="mode === 1 ? '送出' : '儲存'"></button>
            </div>
        </form>
    </dialog>
</div>
<script>
    window.__ITEMS__ = <?= json_encode(\yii\helpers\ArrayHelper::toArray($items)) ?>;
    window.__DEPARTMENTS__ = <?= json_encode(\yii\helpers\ArrayHelper::toArray($departments)) ?>;
</script>
