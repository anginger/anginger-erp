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
$this->beginPage();
?><!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Html::encode($this->title ?: 'Anginger ERP') ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<header class="topbar">
    <div class="brand">Anginger ERP</div>
    <nav class="nav">
        <a href="<?= Url::to(['/site/index']) ?>">總覽</a>
        <a href="<?= Url::to(['/product']) ?>">產品</a>
        <a href="<?= Url::to(['/provider']) ?>">供應商</a>
        <a href="<?= Url::to(['/user']) ?>">使用者</a>
        <a href="<?= Url::to(['/level']) ?>">權限等級</a>
        <a href="<?= Url::to(['/history']) ?>">操作紀錄</a>
    </nav>
    <div class="user">
        <?php if (!Yii::$app->user->isGuest): ?>
            <span><?= Html::encode(Yii::$app->user->identity->display_name) ?></span>
            <a href="<?= Url::to(['/site/logout']) ?>">登出</a>
        <?php endif; ?>
    </div>
</header>
<main class="container">
    <?= $content ?>
</main>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
