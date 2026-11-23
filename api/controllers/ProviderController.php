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

namespace anginger\controllers;

use anginger\models\Provider;
use yii\db\ActiveRecord;

/**
 * Port of `Slim\Controllers\Provider` (main branch).
 *
 *   GET    /provider?id=...  POST /provider  PUT /provider  DELETE /provider?id=...  GET /provider/all
 *
 * Note: the old getOne read a `uuid` query param but loaded by the integer
 * `_id` primary key (which always threw a TypeError); the frontend never
 * called it. This port uses `id`, matching the delete/update calls.
 */
class ProviderController extends ResourceController
{
    protected function modelClass(): string
    {
        return Provider::class;
    }

    protected function pkParam(): string
    {
        return 'id';
    }

    protected function requiredCreateFields(): array
    {
        return ['display_name', 'contact_name', 'contact_phone', 'contact_address'];
    }

    protected function requiredUpdateFields(): array
    {
        return ['id', 'display_name', 'contact_name', 'contact_phone', 'contact_address'];
    }

    protected function fillCreate(array $form): ActiveRecord
    {
        $provider = new Provider();
        $this->fillUpdate($provider, $form);
        return $provider;
    }

    protected function fillUpdate(ActiveRecord $model, array $form): void
    {
        /** @var Provider $model */
        $model->display_name = strval($form['display_name']);
        $model->contact_name = strval($form['contact_name']);
        $model->contact_phone = strval($form['contact_phone']);
        $model->contact_address = strval($form['contact_address']);
    }
}
