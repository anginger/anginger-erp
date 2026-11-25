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

use anginger\modules\history\assets\HistoryAsset;

/* @var $this \yii\web\View */
/* @var $items \anginger\models\History[] */

$this->title = '操作紀錄 - Anginger ERP';
HistoryAsset::register($this);
?>
<div x-data="historyPage()" x-init="load()">
    <div class="page-head">
        <h1>操作紀錄</h1>
        <button class="ts-button is-outlined" @click="load()">重新整理</button>
    </div>
    <div class="ts-box is-flush">
        <table class="ts-table">
            <thead>
            <tr><th>時間</th><th>使用者</th><th>方法</th><th>資源</th></tr>
            </thead>
            <tbody>
            <template x-for="h in items" :key="h._id">
                <tr>
                    <td x-text="ts(h.created_time)"></td>
                    <td x-text="h.user_name || h.user"></td>
                    <td><span class="ts-chip" :class="'m-' + (h.method || '').toLowerCase()" x-text="h.method"></span></td>
                    <td x-text="h.resource"></td>
                </tr>
            </template>
            <tr x-show="items.length === 0"><td colspan="4" class="empty">尚無紀錄</td></tr>
            </tbody>
        </table>
    </div>
</div>
<script>window.__ITEMS__ = <?= \yii\helpers\Json::htmlEncode($items) ?>;</script>
