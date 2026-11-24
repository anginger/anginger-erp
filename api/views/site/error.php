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
/* @var $exception \Throwable */
$this->title = '錯誤 - Anginger ERP';
?>
<div class="login-panel" style="min-height:100vh">
    <div class="login-card">
        <h1><?= Html::encode($this->title) ?></h1>
        <p class="error"><?= Html::encode($exception->getMessage()) ?></p>
        <p><a href="/">回到首頁</a></p>
    </div>
</div>
