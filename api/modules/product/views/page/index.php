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

use anginger\modules\product\assets\ProductAsset;

/* @var $this \yii\web\View */
/* @var $items \anginger\models\Product[] */

$this->title = '產品資料 - Anginger ERP';
ProductAsset::register($this);
?>
<div x-data="productPage()">
    <div class="page-head">
        <div class="ts-input app-search"><input type="search" placeholder="搜尋…" x-model="q" aria-label="搜尋列表"></div>
        <h1>產品資料</h1>
        <button class="ts-button" @click="openCreate()">+ 新增產品</button>
    </div>
    <div class="ts-box is-flush">
        <table class="ts-table">
            <thead>
            <tr><th>名稱</th><th>成本</th><th>售價</th><th>數量</th><th></th></tr>
            </thead>
            <tbody>
            <template x-for="p in filtered" :key="p.uuid">
                <tr>
                    <td x-text="p.display_name"></td>
                    <td x-text="p.cost_value"></td>
                    <td x-text="p.sell_value"></td>
                    <td x-text="p.remain_amount"></td>
                    <td>
                        <button class="ts-button is-small is-outlined" @click="openEdit(p)">編輯</button>
                        <button class="ts-button is-small is-negative is-outlined" @click="remove(p)">刪除</button>
                    </td>
                </tr>
            </template>
            <tr x-show="filtered().length === 0"><td colspan="5" class="empty">尚無資料</td></tr>
            </tbody>
        </table>
    </div>

    <dialog class="ts-modal" :open="mode !== 0" @click.self="mode = 0">
        <form class="content" @submit.prevent="submit()">
            <h2 x-text="mode === 1 ? '新增產品' : '編輯產品'"></h2>
            <p class="error" aria-live="polite" x-show="message" x-text="message"></p>
            <label class="app-field">名稱<div class="ts-input"><input name="display_name" autocomplete="off" x-model="form.display_name"></div></label>
            <label class="app-field">成本<div class="ts-input"><input type="number" name="cost_value" autocomplete="off" x-model="form.cost_value"></div></label>
            <label class="app-field">售價<div class="ts-input"><input type="number" name="sell_value" autocomplete="off" x-model="form.sell_value"></div></label>
            <label class="app-field">數量<div class="ts-input"><input type="number" name="remain_amount" autocomplete="off" x-model="form.remain_amount"></div></label>
            <div class="ts-buttons is-end-aligned">
                <button class="ts-button is-outlined" type="button" @click="mode = 0">取消</button>
                <button class="ts-button" type="submit" x-text="mode === 1 ? '送出' : '儲存'"></button>
            </div>
        </form>
    </dialog>
</div>
<script>window.__ITEMS__ = <?= \yii\helpers\Json::htmlEncode(\yii\helpers\ArrayHelper::toArray($items)) ?>;</script>
