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

use anginger\modules\provider\assets\ProviderAsset;

/* @var $this \yii\web\View */
/* @var $items \anginger\models\Provider[] */

$this->title = '供應商資料 - Anginger ERP';
ProviderAsset::register($this);
?>
<div x-data="providerPage()">
    <div class="page-head">
        <h1>供應商資料</h1>
        <button class="ts-button" @click="openCreate()">+ 新增供應商</button>
    </div>
    <div class="ts-box">
        <table class="ts-table">
            <thead>
            <tr><th>名稱</th><th>聯絡人</th><th>電話</th><th>地址</th><th></th></tr>
            </thead>
            <tbody>
            <template x-for="p in items" :key="p._id">
                <tr>
                    <td x-text="p.display_name"></td>
                    <td x-text="p.contact_name"></td>
                    <td x-text="p.contact_phone"></td>
                    <td x-text="p.contact_address"></td>
                    <td>
                        <button class="ts-button is-small is-outlined" @click="openEdit(p)">編輯</button>
                        <button class="ts-button is-small is-negative" @click="remove(p)">刪除</button>
                    </td>
                </tr>
            </template>
            <tr x-show="items.length === 0"><td colspan="5" class="empty">尚無資料</td></tr>
            </tbody>
        </table>
    </div>

    <dialog class="ts-modal" :open="mode !== 0" @click.self="mode = 0">
        <form class="content" @submit.prevent="submit()">
            <h2 x-text="mode === 1 ? '新增供應商' : '編輯供應商'"></h2>
            <p class="error" aria-live="polite" x-show="message" x-text="message"></p>
            <label class="app-field">名稱<div class="ts-input"><input name="display_name" autocomplete="off" x-model="form.display_name"></div></label>
            <label class="app-field">聯絡人<div class="ts-input"><input name="contact_name" autocomplete="off" x-model="form.contact_name"></div></label>
            <label class="app-field">電話<div class="ts-input"><input type="tel" name="contact_phone" autocomplete="off" x-model="form.contact_phone"></div></label>
            <label class="app-field">地址<div class="ts-input"><input name="contact_address" autocomplete="off" x-model="form.contact_address"></div></label>
            <div class="ts-buttons is-end-aligned">
                <button class="ts-button is-outlined" type="button" @click="mode = 0">取消</button>
                <button class="ts-button" type="submit" x-text="mode === 1 ? '送出' : '儲存'"></button>
            </div>
        </form>
    </dialog>
</div>
<script>window.__ITEMS__ = <?= json_encode($items) ?>;</script>
