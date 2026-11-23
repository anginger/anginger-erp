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

return [
    'id' => 'anginger-erp',
    'basePath' => __DIR__,
    'controllerNamespace' => 'anginger\\controllers',
    'aliases' => [
        '@anginger' => __DIR__,
    ],
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'sqlite:@anginger/database.sqlite',
        ],
        'request' => [
            // required for the session cookie
            'cookieValidationKey' => 'anginger-erp-dev-key-change-me-in-production',
            // this is a pure JSON API (the old Slim framework had no CSRF either)
            'enableCsrfValidation' => false,
            // the frontend posts URLSearchParams; also accept JSON bodies
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        // NOTE: no global response FORMAT_JSON — pages are server-rendered HTML,
        // API controllers return JSON explicitly via asJson().
        'user' => [
            'identityClass' => 'anginger\models\User',
            'enableSession' => true,
            'enableAutoLogin' => false,
            'loginUrl' => null, // API returns 401/403 JSON instead of redirecting
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                // pages
                'login' => 'site/login',
                'logout' => 'site/logout',
                // every business domain is a module with its own page + JSON API
                '<module:product|provider|user|level|history>' => '<module>/page/index',
                // JSON API consumed by the Alpine.js frontend (fetch)
                'api/authentic/<action:\w+>' => 'authentic/<action>',
                'api/<module:product|provider|user|level|history>' => '<module>/api/index',
                'api/<module:product|provider|user|level|history>/<action:\w+>' => '<module>/api/<action>',
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
    ],
    // application-level modules (NOT a component): each business domain
    // owns its page route and its /api/* JSON API
    'modules' => [
        'product' => 'anginger\modules\product\Module',
        'provider' => 'anginger\modules\provider\Module',
        'user' => 'anginger\modules\user\Module',
        'level' => 'anginger\modules\level\Module',
        'history' => 'anginger\modules\history\Module',
    ],
];
