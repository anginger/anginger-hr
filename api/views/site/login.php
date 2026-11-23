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
    <form class="ts-box login-wrap" x-data="loginForm()" @submit.prevent="submit()">
        <h1>Anginger HR</h1>
        <p class="muted">人力資源管理系統 - 請登入繼續</p>
        <p class="error" x-show="message" x-text="message" aria-live="polite"></p>
        <label class="app-field">帳號
            <div class="ts-input"><input name="username" autocomplete="username" spellcheck="false" x-model="username" autofocus></div>
        </label>
        <label class="app-field">密碼
            <div class="ts-input"><input type="password" name="password" autocomplete="current-password" x-model="password"></div>
        </label>
        <button class="ts-button is-fluid" type="submit" :disabled="loading" x-text="loading ? '登入中…' : '登入'"></button>
    </form>
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
