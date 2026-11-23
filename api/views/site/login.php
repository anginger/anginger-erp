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
<div class="ts-app-center" style="min-height:100vh">
    <div class="ts-box login-wrap" x-data="loginForm()">
        <h1>Anginger ERP</h1>
        <p class="muted">請登入繼續</p>
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
