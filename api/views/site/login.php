<?php
/**
 * Copyright 2025 Anginger Platform
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/* @var $this \yii\web\View */
$this->title = '登入 - Anginger ERP';
?>
<div class="login-split">
    <aside class="login-hero">
        <div class="brand"><span class="brand-mark">A</span><span class="brand-name">Anginger ERP</span></div>
        <h1>進銷存與供應商，<br>一個後台管好。</h1>
        <p class="hero-sub">產品、供應商、使用者與權限集中管理，異動全程留痕 —適合臺灣中小企業的輕量 ERP。</p>
        <ul class="hero-feats">
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8l-9-5-9 5v8l9 5 9-5V8z"/><path d="M3.3 8.3 12 13l8.7-4.7"/><path d="M12 22V13"/></svg>產品價格與庫存</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 4h14v12H1z"/><path d="M15 9h4l4 4v3h-8V9z"/><circle cx="6" cy="18.5" r="2"/><circle cx="18" cy="18.5" r="2"/></svg>供應商與聯絡資料</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>使用者與權限等級</li>
            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 6h13M8 12h13M8 18h13"/><path d="M3 6h.01M3 12h.01M3 18h.01"/></svg>操作紀錄完整留痕</li>
        </ul>
        <p class="hero-foot">Anginger 工作台 · ERP</p>
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
