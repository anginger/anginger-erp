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

namespace anginger\modules\provider\controllers;

use anginger\controllers\ResourceController;
use anginger\models\Provider;
use yii\db\ActiveRecord;
use anginger\models\User;
use yii\web\Response;
use Yii;

/**
 * JSON API for providers, mounted under /api/provider.
 */
class ApiController extends ResourceController
{
    public function beforeAction($action): bool
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        $identity = Yii::$app->user->identity;
        if (!$identity instanceof User || !$identity->isAdmin()) {
            Yii::$app->response->setStatusCode(403);
            Yii::$app->response->format = Response::FORMAT_JSON;
            Yii::$app->response->data = ['message' => 'forbidden'];
            $this->logHistory();
            return false;
        }
        return true;
    }


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
