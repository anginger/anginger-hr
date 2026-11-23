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
        <button class="btn primary" @click="openCreate()">+ 新增部門</button>
    </div>
    <div class="card">
        <table>
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
                        <button class="btn" @click="openEdit(d)">編輯</button>
                        <button class="btn danger" @click="remove(d)">刪除</button>
                    </td>
                </tr>
            </template>
            <tr x-show="items.length === 0"><td colspan="4" class="empty">尚無資料</td></tr>
            </tbody>
        </table>
    </div>

    <div class="modal" x-show="mode !== 0" @click.self="mode = 0">
        <div class="card modal-card">
            <h2 x-text="mode === 1 ? '新增部門' : '編輯部門'"></h2>
            <p class="error" x-show="message" x-text="message"></p>
            <label>部門名稱<input x-model="form.dep_name"></label>
            <label>主管
                <select x-model="form.manager_emp_id">
                    <template x-for="e in employees" :key="e.emp_id">
                        <option :value="e.emp_id" x-text="empLabel(e)"></option>
                    </template>
                </select>
            </label>
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
