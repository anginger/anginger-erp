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
        <h1>產品資料</h1>
        <button class="btn primary" @click="openCreate()">+ 新增產品</button>
    </div>
    <div class="card">
        <table>
            <thead>
            <tr><th>名稱</th><th>成本</th><th>售價</th><th>數量</th><th></th></tr>
            </thead>
            <tbody>
            <template x-for="p in items" :key="p.uuid">
                <tr>
                    <td x-text="p.display_name"></td>
                    <td x-text="p.cost_value"></td>
                    <td x-text="p.sell_value"></td>
                    <td x-text="p.remain_amount"></td>
                    <td>
                        <button class="btn" @click="openEdit(p)">編輯</button>
                        <button class="btn danger" @click="remove(p)">刪除</button>
                    </td>
                </tr>
            </template>
            <tr x-show="items.length === 0"><td colspan="5" class="empty">尚無資料</td></tr>
            </tbody>
        </table>
    </div>

    <div class="modal" x-show="mode !== 0" @click.self="mode = 0">
        <div class="card modal-card">
            <h2 x-text="mode === 1 ? '新增產品' : '編輯產品'"></h2>
            <p class="error" x-show="message" x-text="message"></p>
            <label>名稱<input x-model="form.display_name"></label>
            <label>成本<input type="number" x-model="form.cost_value"></label>
            <label>售價<input type="number" x-model="form.sell_value"></label>
            <label>數量<input type="number" x-model="form.remain_amount"></label>
            <div class="row">
                <button class="btn" @click="mode = 0">取消</button>
                <button class="btn primary" @click="submit()" x-text="mode === 1 ? '送出' : '儲存'"></button>
            </div>
        </div>
    </div>
</div>
<script>window.__ITEMS__ = <?= json_encode($items) ?>;</script>
