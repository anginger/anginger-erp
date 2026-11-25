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
$this->beginPage();
?><!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= Html::encode(Yii::$app->request->csrfToken) ?>">
    <script>
    // Attach the CSRF token to every state-changing same-origin fetch().
    (function () {
        var orig = window.fetch.bind(window);
        window.fetch = function (url, opts) {
            opts = opts || {};
            var m = (opts.method || 'GET').toUpperCase();
            var sameOrigin = String(url).charAt(0) === '/' || String(url).indexOf(location.origin) === 0;
            if (sameOrigin && m !== 'GET' && m !== 'HEAD') {
                var meta = document.querySelector('meta[name="csrf-token"]');
                if (meta) { opts.headers = Object.assign({ 'X-CSRF-Token': meta.content }, opts.headers || {}); }
            }
            return orig(url, opts);
        };
    })();
    </script>

    <meta name="theme-color" content="#303030">
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='7' fill='%23303030'/%3E%3Ctext x='16' y='22' font-size='16' text-anchor='middle' fill='%23fff' font-family='sans-serif' font-weight='bold'%3EA%3C/text%3E%3C/svg%3E">
    <title><?= Html::encode($this->title ?: 'Anginger ERP') ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<?= $content ?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
