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

use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $histories \anginger\models\History[] */
$this->title = '總覽 - Anginger ERP';
?>
<div class="card">
    <h1>總覽</h1>
    <p>歡迎，<?= Html::encode(Yii::$app->user->identity->display_name) ?>。</p>
    <p class="muted">Anginger ERP 是一款輕量級 ERP 系統，面向臺灣客戶設計。</p>
</div>
<div class="card" x-data="historyWidget()" x-init="load()">
    <div class="page-head">
        <h2>操作紀錄</h2>
        <button class="btn" @click="load()">重新整理</button>
    </div>
    <table>
        <thead>
        <tr><th>時間</th><th>使用者</th><th>方法</th><th>資源</th></tr>
        </thead>
        <tbody>
        <template x-for="h in items" :key="h._id">
            <tr>
                <td x-text="ts(h.created_time)"></td>
                <td x-text="h.user"></td>
                <td><span class="badge" x-text="h.method"></span></td>
                <td x-text="h.resource"></td>
            </tr>
        </template>
        <tr x-show="items.length === 0"><td colspan="4" class="empty">尚無紀錄</td></tr>
        </tbody>
    </table>
</div>
<script>
window.__HISTORIES__ = <?= json_encode($histories) ?>;
document.addEventListener('alpine:init', () => {
    Alpine.data('historyWidget', () => ({
        items: window.__HISTORIES__ || [],
        async load() {
            const r = await fetch('/api/history/all', {credentials: 'same-origin'});
            if (r.ok) this.items = await r.json();
        },
        ts(t) { return new Date(t * 1000).toLocaleString(); }
    }));
});
</script>
