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

use anginger\modules\level\assets\LevelAsset;

/* @var $this \yii\web\View */
/* @var $items \anginger\models\Level[] */

$this->title = '權限等級 - Anginger ERP';
LevelAsset::register($this);
?>
<div x-data="levelPage()">
    <div class="page-head">
        <h1>權限等級</h1>
        <button class="ts-button" @click="openCreate()">+ 新增等級</button>
    </div>
    <div class="ts-box is-flush">
        <table class="ts-table">
            <thead>
            <tr><th>ID</th><th>名稱</th><th></th></tr>
            </thead>
            <tbody>
            <template x-for="l in items" :key="l._id">
                <tr>
                    <td x-text="l._id"></td>
                    <td x-text="l.display_name"></td>
                    <td>
                        <button class="ts-button is-small is-outlined" @click="openEdit(l)">編輯</button>
                        <button class="ts-button is-small is-negative is-outlined" @click="remove(l)">刪除</button>
                    </td>
                </tr>
            </template>
            <tr x-show="items.length === 0"><td colspan="3" class="empty">尚無資料</td></tr>
            </tbody>
        </table>
    </div>

    <dialog class="ts-modal" :open="mode !== 0" @click.self="mode = 0">
        <form class="content" @submit.prevent="submit()">
            <h2 x-text="mode === 1 ? '新增等級' : '編輯等級'"></h2>
            <p class="error" aria-live="polite" x-show="message" x-text="message"></p>
            <label class="app-field">名稱<div class="ts-input"><input name="display_name" autocomplete="off" x-model="form.display_name"></div></label>
            <div class="ts-buttons is-end-aligned">
                <button class="ts-button is-outlined" type="button" @click="mode = 0">取消</button>
                <button class="ts-button" type="submit" x-text="mode === 1 ? '送出' : '儲存'"></button>
            </div>
        </form>
    </dialog>
</div>
<script>window.__ITEMS__ = <?= json_encode(\yii\helpers\ArrayHelper::toArray($items)) ?>;</script>
