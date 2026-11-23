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

namespace anginger\assets;

use yii\web\AssetBundle;

/**
 * Global assets: TocasUI (vendored, no CDN dependency) + project overrides,
 * plus Alpine.js (vendored). Every module asset bundle depends on this one.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'assets/vendor/tocas.min.css',
        'assets/app.css',
    ];
    public $js = ['assets/alpine.min.js'];
}
