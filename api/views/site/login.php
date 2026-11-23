<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

/* @var $this \yii\web\View */
$this->title = '登入 - Anginger HR';
?>
<div class="ts-app-center" style="min-height:100vh">
    <div class="ts-box login-wrap" x-data="loginForm()">
        <h1>Anginger HR</h1>
        <p class="muted">人力資源管理系統 - 請登入繼續</p>
        <p class="error" x-show="message" x-text="message"></p>
        <label class="app-field">帳號
            <div class="ts-input"><input x-model="username" @keyup.enter="submit()" autofocus></div>
        </label>
        <label class="app-field">密碼
            <div class="ts-input"><input type="password" x-model="password" @keyup.enter="submit()"></div>
        </label>
        <button class="ts-button is-fluid" @click="submit()" :disabled="loading">登入</button>
    </div>
</div>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('loginForm', () => ({
        username: '',
        password: '',
        message: '',
        loading: false,
        async submit() {
            this.loading = true;
            this.message = '';
            try {
                const r = await fetch('/api/authentic/session', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: new URLSearchParams({username: this.username, password: this.password})
                });
                if (r.status === 201) {
                    location.href = '/';
                    return;
                }
                const d = await r.json().catch(() => ({}));
                this.message = d.message || '登入失敗';
            } catch (e) {
                this.message = '無法連線到伺服器';
            }
            this.loading = false;
        }
    }));
});
</script>
