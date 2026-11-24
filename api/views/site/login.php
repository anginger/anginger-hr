<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

/* @var $this \yii\web\View */
$this->title = '登入 - Anginger HR';
?>
<div class="login-split">
    <aside class="login-hero">
        <div class="brand"><span class="brand-mark">A</span><span class="brand-name">Anginger HR</span></div>
        <h1>人資管理，<br>每天省下一小時。</h1>
        <p class="hero-sub">員工、部門與帳號集中管理，薪資、假別與操作紀錄一目了然 — 老闆與人資每天打開這裡就夠。</p>
        <ul class="hero-feats">
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>員工與部門資料集中維護</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>帳號與登入權限控管</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 6h13M8 12h13M8 18h13"/><path d="M3 6h.01M3 12h.01M3 18h.01"/></svg>每筆異動都有操作紀錄</li>
        </ul>
        <p class="hero-foot">Anginger 工作台 · HR</p>
    </aside>
    <div class="login-panel">
        <form class="login-card" x-data="loginForm()" @submit.prevent="submit()">
            <h2>登入</h2>
            <p class="muted">歡迎回來，請輸入帳號密碼。</p>
            <p class="error" aria-live="polite" x-show="message" x-text="message"></p>
            <label class="app-field">帳號
                <span class="ts-input"><input name="username" autocomplete="username" spellcheck="false" x-model="username" autofocus></span>
            </label>
            <label class="app-field">密碼
                <span class="ts-input"><input type="password" name="password" autocomplete="current-password" x-model="password"></span>
            </label>
            <button class="ts-button is-fluid" type="submit" :disabled="loading" x-text="loading ? '登入中…' : '登入'"></button>
            <p class="muted ldap-hint" x-show="ldap" x-cloak>已啟用 LDAP/AD 登入 — 內建帳號與目錄帳號皆可登入</p>
        </form>
    </div>
</div>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('loginForm', () => ({
        username: '',
        password: '',
        message: '',
        loading: false,
        ldap: false,
        async init() {
            try {
                const r = await fetch('/api/authentic/ldap', {credentials: 'same-origin'});
                if (r.ok) { const d = await r.json(); this.ldap = !!d.enabled; }
            } catch (e) { /* hint is optional */ }
        },
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
