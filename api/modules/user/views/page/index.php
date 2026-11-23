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
<div x-data="userPage()">
    <div class="page-head">
        <h1>使用者管理</h1>
        <button class="btn primary" @click="openCreate()">+ 新增使用者</button>
    </div>
    <div class="card">
        <table>
            <thead>
            <tr><th>帳號</th><th>姓名</th><th>等級</th><th>Email</th><th>電話</th><th></th></tr>
            </thead>
            <tbody>
            <template x-for="u in items" :key="u.uuid">
                <tr>
                    <td x-text="u.username"></td>
                    <td x-text="u.display_name"></td>
                    <td x-text="levelName(u.level)"></td>
                    <td x-text="u.email"></td>
                    <td x-text="u.phone"></td>
                    <td>
                        <button class="btn" @click="openEdit(u)">編輯</button>
                        <button class="btn danger" @click="remove(u)">刪除</button>
                    </td>
                </tr>
            </template>
            <tr x-show="items.length === 0"><td colspan="6" class="empty">尚無資料</td></tr>
            </tbody>
        </table>
    </div>

    <div class="modal" x-show="mode !== 0" @click.self="mode = 0">
        <div class="card modal-card">
            <h2 x-text="mode === 1 ? '新增使用者' : '編輯使用者'"></h2>
            <p class="error" x-show="message" x-text="message"></p>
            <label>帳號<input x-model="form.username"></label>
            <label>密碼（留空表示不變）<input type="password" x-model="form.password"></label>
            <label>等級
                <select x-model="form.level">
                    <template x-for="l in levels" :key="l._id">
                        <option :value="l._id" x-text="l.display_name"></option>
                    </template>
                </select>
            </label>
            <label>姓名<input x-model="form.display_name"></label>
            <label>地址<input x-model="form.address"></label>
            <label>Email<input type="email" x-model="form.email"></label>
            <label>電話<input x-model="form.phone"></label>
            <div class="row">
                <button class="btn" @click="mode = 0">取消</button>
                <button class="btn primary" @click="submit()" x-text="mode === 1 ? '送出' : '儲存'"></button>
            </div>
        </div>
    </div>
</div>
<script>
    window.__ITEMS__ = <?= json_encode($items) ?>;
    window.__LEVELS__ = <?= json_encode($levels) ?>;
</script>
