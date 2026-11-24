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

use anginger\assets\AppAsset;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */

AppAsset::register($this);
$path = Yii::$app->request->pathInfo;
if ($path === 'site/index') { $path = ''; }
$me = Yii::$app->user->isGuest ? null : Yii::$app->user->identity;
$meName = $me ? ($me->display_name ?? $me->username) : '';
$this->beginPage();
?><!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#5865F2">
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%235865F2'/%3E%3Ctext x='16' y='22' font-size='16' text-anchor='middle' fill='%23fff' font-family='sans-serif' font-weight='bold'%3EA%3C/text%3E%3C/svg%3E">
    <script>
    (function () {{
        var t = null;
        try {{ t = localStorage.getItem('anginger-theme'); }} catch (e) {{}}
        if (t !== 'light' && t !== 'dark') {{
            t = window.matchMedia && matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }}
        document.documentElement.dataset.theme = t;
    }})();
    </script>
    <title><?= Html::encode($this->title ?: 'Anginger ERP') ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<a class="skip-link" href="#main">跳到主要內容</a>
<header class="ts-app-topbar app-topbar">
    <div class="breathing-bar" aria-hidden="true"></div>
    <div class="start">
        <a class="brand" href="/"><span class="brand-mark">A</span><span class="brand-name">Anginger ERP</span></a>
    </div>
    <div class="center">
        <nav class="ts-menu app-nav" aria-label="主選單">
            <a class="item<?= $path === '' ? ' is-active' : '' ?>" href="/">總覽</a>
            <a class="item<?= $path === 'product' ? ' is-active' : '' ?>" href="/product">產品</a>
            <a class="item<?= $path === 'provider' ? ' is-active' : '' ?>" href="/provider">供應商</a>
            <a class="item<?= $path === 'user' ? ' is-active' : '' ?>" href="/user">使用者</a>
            <a class="item<?= $path === 'level' ? ' is-active' : '' ?>" href="/level">權限等級</a>
            <a class="item<?= $path === 'history' ? ' is-active' : '' ?>" href="/history">操作紀錄</a>
        </nav>
    </div>
    <div class="end">
        <?php if ($me): ?>
        <button type="button" id="themeToggle" class="ts-button is-icon is-ghost app-icon-btn" aria-label="切換深色模式" title="切換深色模式">
            <svg class="ic-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
            <svg class="ic-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg>
        </button>
        <span class="user-chip"><span class="avatar"><?= Html::encode(mb_strtoupper(mb_substr($meName, 0, 1))) ?></span><?= Html::encode($meName) ?></span>
        <a class="ts-button is-icon is-ghost app-icon-btn" href="/site/logout" aria-label="登出" title="登出">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
        </a>
        <?php endif; ?>
    </div>
</header>
<main class="ts-container app-main" id="main">
    <?= $content ?>
</main>
<script>
(function () {
    var b = document.getElementById('themeToggle');
    if (!b) return;
    b.addEventListener('click', function () {
        var r = document.documentElement;
        var n = r.dataset.theme === 'dark' ? 'light' : 'dark';
        r.dataset.theme = n;
        try { localStorage.setItem('anginger-theme', n); } catch (e) {}
    });
})();
</script>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
