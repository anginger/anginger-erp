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
use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $content string */

AppAsset::register($this);
$path = Yii::$app->request->pathInfo;
$this->beginPage();
?><!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#303030">
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='7' fill='%23303030'/%3E%3Ctext x='16' y='22' font-size='16' text-anchor='middle' fill='%23fff' font-family='sans-serif' font-weight='bold'%3EA%3C/text%3E%3C/svg%3E">
    <title><?= Html::encode($this->title ?: 'Anginger ERP') ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<a class="skip-link" href="#main">跳到主要內容</a>
<header class="ts-app-topbar">
    <div class="start">
        <div class="item is-text"><strong>Anginger ERP</strong></div>
    </div>
    <div class="center">
        <nav class="ts-menu">
            <a class="item<?= $path === '' ? ' is-active' : '' ?>" href="<?= Url::to(['/site/index']) ?>">總覽</a>
            <a class="item<?= $path === 'product' ? ' is-active' : '' ?>" href="<?= Url::to(['/product']) ?>">產品</a>
            <a class="item<?= $path === 'provider' ? ' is-active' : '' ?>" href="<?= Url::to(['/provider']) ?>">供應商</a>
            <a class="item<?= $path === 'user' ? ' is-active' : '' ?>" href="<?= Url::to(['/user']) ?>">使用者</a>
            <a class="item<?= $path === 'level' ? ' is-active' : '' ?>" href="<?= Url::to(['/level']) ?>">權限等級</a>
            <a class="item<?= $path === 'history' ? ' is-active' : '' ?>" href="<?= Url::to(['/history']) ?>">操作紀錄</a>
        </nav>
    </div>
    <div class="end">
        <?php if (!Yii::$app->user->isGuest): ?>
            <div class="item is-text"><?= Html::encode(Yii::$app->user->identity->display_name) ?></div>
            <a class="item" href="<?= Url::to(['/site/logout']) ?>">登出</a>
        <?php endif; ?>
    </div>
</header>
<main class="ts-container app-main" id="main">
    <?= $content ?>
</main>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
