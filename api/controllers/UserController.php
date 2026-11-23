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

use anginger\models\User;
use yii\db\ActiveRecord;

/**
 * Port of `Slim\Controllers\User` (main branch).
 *
 *   GET    /user?uuid=...  POST /user  PUT /user  DELETE /user?uuid=...  GET /user/all
 */
class UserController extends ResourceController
{
    protected function modelClass(): string
    {
        return User::class;
    }

    protected function pkParam(): string
    {
        return 'uuid';
    }

    protected function requiredCreateFields(): array
    {
        return ['username', 'password', 'level', 'display_name', 'address', 'email', 'phone'];
    }

    protected function requiredUpdateFields(): array
    {
        // password is optional on update: when empty the old hash is kept
        return ['uuid', 'username', 'level', 'display_name', 'address', 'email', 'phone'];
    }

    protected function fillCreate(array $form): ActiveRecord
    {
        $user = new User();
        $user->uuid = self::uuidV7();
        $user->username = strval($form['username']);
        $user->password = hash('sha256', strval($form['password']));
        $user->level = intval($form['level']);
        $user->display_name = strval($form['display_name']);
        $user->created_time = time();
        $user->address = strval($form['address']);
        $user->email = strval($form['email']);
        $user->phone = strval($form['phone']);
        return $user;
    }

    protected function fillUpdate(ActiveRecord $model, array $form): void
    {
        /** @var User $model */
        $model->username = strval($form['username']);
        if (!empty($form['password'])) {
            $model->password = hash('sha256', strval($form['password']));
        }
        $model->level = intval($form['level']);
        $model->display_name = strval($form['display_name']);
        $model->address = strval($form['address']);
        $model->email = strval($form['email']);
        $model->phone = strval($form['phone']);
    }
}
