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
        <button class="btn primary" @click="openCreate()">+ 新增員工</button>
    </div>
    <div class="card">
        <table>
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
                        <button class="btn" @click="openEdit(e)">編輯</button>
                        <button class="btn danger" @click="remove(e)">刪除</button>
                    </td>
                </tr>
            </template>
            <tr x-show="items.length === 0"><td colspan="8" class="empty">尚無資料</td></tr>
            </tbody>
        </table>
    </div>

    <div class="modal" x-show="mode !== 0" @click.self="mode = 0">
        <div class="card modal-card">
            <h2 x-text="mode === 1 ? '新增員工' : '編輯員工'"></h2>
            <p class="error" x-show="message" x-text="message"></p>
            <label>姓名<input x-model="form.emp_name"></label>
            <label>職稱<input x-model="form.job_title"></label>
            <label>部門
                <select x-model="form.dep_id">
                    <template x-for="d in departments" :key="d.dep_id">
                        <option :value="d.dep_id" x-text="d.dep_name"></option>
                    </template>
                </select>
            </label>
            <label>縣市<input x-model="form.city"></label>
            <label>地址<input x-model="form.address"></label>
            <label>電話<input x-model="form.phone"></label>
            <label>郵遞區號<input x-model="form.zip_code"></label>
            <label>月薪<input type="number" x-model="form.month_salary"></label>
            <label>年假天數<input type="number" x-model="form.annual_leave"></label>
            <div class="row">
                <button class="btn" @click="mode = 0">取消</button>
                <button class="btn primary" @click="submit()" x-text="mode === 1 ? '送出' : '儲存'"></button>
            </div>
        </div>
    </div>
</div>
<script>
    window.__ITEMS__ = <?= json_encode($items) ?>;
    window.__DEPARTMENTS__ = <?= json_encode($departments) ?>;
</script>
