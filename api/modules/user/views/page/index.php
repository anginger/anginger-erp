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

use anginger\modules\user\assets\UserAsset;

/* @var $this \yii\web\View */
/* @var $items \anginger\models\User[] */
/* @var $levels \anginger\models\Level[] */

$this->title = '使用者管理 - Anginger ERP';
UserAsset::register($this);
?>
<div x-data="userPage()" x-init="refresh()">
    <div class="page-head">
        <div class="ts-input app-search"><input type="search" placeholder="搜尋…" x-model="q" aria-label="搜尋列表"></div>
        <h1>使用者管理</h1>
        <button class="ts-button" @click="openCreate()" :disabled="ldap" :title="ldap ? 'LDAP/AD 已啟用，帳號由目錄同步，無法手動新增' : ''">+ 新增使用者</button>
    </div>
    <p class="muted" x-show="ldap" x-cloak>LDAP/AD 登入已啟用：內建註冊已停用，新帳號會在使用者首次登入時自動同步；內建帳號仍可使用本機密碼登入。</p>
    <div class="ts-box is-flush">
        <table class="ts-table">
            <thead>
            <tr><th>帳號</th><th>姓名</th><th>等級</th><th>Email</th><th>電話</th><th>來源</th><th>狀態</th><th></th></tr>
            </thead>
            <tbody>
            <template x-for="u in filtered" :key="u.uuid">
                <tr :class="{archived: u.archived}">
                    <td x-text="u.username"></td>
                    <td><span class="cell-user"><span class="avatar" x-text="(u.display_name || u.username || '?').charAt(0).toUpperCase()"></span><span x-text="u.display_name"></span></span></td>
                    <td x-text="levelName(u.level)"></td>
                    <td x-text="u.email"></td>
                    <td x-text="u.phone"></td>
                    <td><span class="ts-chip" :class="u.auth_source === 'ldap' ? 'm-ldap' : 'm-local'" x-text="u.auth_source === 'ldap' ? 'LDAP' : '本地'"></span></td>
                    <td><span class="ts-chip" :class="u.archived ? 'm-archived' : 'm-ok'" x-text="u.archived ? '已封存' : '正常'"></span></td>
                    <td>
                        <button class="ts-button is-small is-outlined" @click="openEdit(u)">編輯</button>
                        <button class="ts-button is-small is-outlined" :class="u.archived ? 'is-positive' : 'is-warning'" @click="toggleArchive(u)" x-text="u.archived ? '解除封存' : '封存'"></button>
                        <button class="ts-button is-small is-negative is-outlined" @click="remove(u)">刪除</button>
                    </td>
                </tr>
            </template>
            <tr x-show="filtered().length === 0"><td colspan="8" class="empty">尚無資料</td></tr>
            </tbody>
        </table>
    </div>

    <dialog class="ts-modal" x-modal="mode !== 0" @modal-close="mode = 0" @click.self="mode = 0">
        <form class="content" @submit.prevent="submit()">
            <h2 x-text="mode === 1 ? '新增使用者' : '編輯使用者'"></h2>
            <p class="error" aria-live="polite" x-show="message" x-text="message"></p>
            <label class="app-field">帳號<div class="ts-input"><input name="username" autocomplete="off" spellcheck="false" x-model="form.username"></div></label>
            <label class="app-field">密碼（留空表示不變）<div class="ts-input"><input type="password" name="password" autocomplete="new-password" x-model="form.password"></div></label>
            <label class="app-field">等級
                <div class="ts-input">
                    <select name="level" x-model="form.level">
                        <template x-for="l in levels" :key="l._id">
                            <option :value="l._id" x-text="l.display_name"></option>
                        </template>
                    </select>
                </div>
            </label>
            <label class="app-field">姓名<div class="ts-input"><input name="display_name" autocomplete="off" x-model="form.display_name"></div></label>
            <label class="app-field">地址<div class="ts-input"><input name="address" autocomplete="off" x-model="form.address"></div></label>
            <label class="app-field">Email<div class="ts-input"><input type="email" name="email" autocomplete="off" x-model="form.email"></div></label>
            <label class="app-field">電話<div class="ts-input"><input type="tel" name="phone" autocomplete="off" x-model="form.phone"></div></label>
            <div class="ts-buttons is-end-aligned">
                <button class="ts-button is-outlined" type="button" @click="mode = 0">取消</button>
                <button class="ts-button" type="submit" x-text="mode === 1 ? '送出' : '儲存'"></button>
            </div>
        </form>
    </dialog>
</div>
<script>
    window.__ITEMS__ = <?= \yii\helpers\Json::htmlEncode(\yii\helpers\ArrayHelper::toArray($items)) ?>;
    window.__LEVELS__ = <?= \yii\helpers\Json::htmlEncode(\yii\helpers\ArrayHelper::toArray($levels)) ?>;
</script>
