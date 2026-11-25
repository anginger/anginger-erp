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

namespace anginger\modules\level\controllers;

use anginger\controllers\ResourceController;
use anginger\models\Level;
use yii\db\ActiveRecord;
use anginger\models\User;
use yii\web\Response;
use Yii;

/**
 * JSON API for permission levels, mounted under /api/level.
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
        return Level::class;
    }

    protected function pkParam(): string
    {
        return 'id';
    }

    protected function requiredCreateFields(): array
    {
        return ['display_name'];
    }

    protected function requiredUpdateFields(): array
    {
        return ['id', 'display_name'];
    }

    protected function fillCreate(array $form): ActiveRecord
    {
        $level = new Level();
        $this->fillUpdate($level, $form);
        return $level;
    }

    protected function fillUpdate(ActiveRecord $model, array $form): void
    {
        /** @var Level $model */
        $model->display_name = strval($form['display_name']);
    }
}
