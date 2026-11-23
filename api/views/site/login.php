<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

/* @var $this \yii\web\View */
$this->title = '登入 - Anginger HR';
?>
<div class="card login-card" x-data="loginForm()">
    <h1>Anginger HR</h1>
    <p class="muted">人力資源管理系統 - 請登入繼續</p>
    <p class="error" x-show="message" x-text="message"></p>
    <label>帳號
        <input x-model="username" @keyup.enter="submit()" autofocus>
    </label>
    <label>密碼
        <input type="password" x-model="password" @keyup.enter="submit()">
    </label>
    <button class="btn primary" style="width:100%" @click="submit()" :disabled="loading">登入</button>
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
