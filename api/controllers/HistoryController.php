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

use anginger\models\History;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * Port of `Slim\Controllers\History` (main branch).
 *
 *   GET /history?id=...  GET /history/all
 *
 * Read-only audit log (written by BaseController::logHistory()).
 * Note: the old getOne read a `uuid` param but loaded by integer `_id`;
 * this port uses `id`, consistent with the table primary key.
 */
class HistoryController extends BaseController
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index' => ['GET'],
                    'all' => ['GET'],
                ],
            ],
        ]);
    }

    public function actionIndex(): Response
    {
        if (!$this->requireLogin()) {
            return $this->forbidden();
        }
        $id = Yii::$app->request->getQueryParam('id');
        if ($id === null || $id === '') {
            return $this->badRequest();
        }
        $history = History::findOne(intval($id));
        return $history !== null ? $this->asJson($history) : $this->notFound();
    }

    public function actionAll(): Response
    {
        if (!$this->requireLogin()) {
            return $this->forbidden();
        }
        // same ordering as the old History::batch(): created_time DESC
        return $this->asJson(History::find()->orderBy(['created_time' => SORT_DESC])->all());
    }
}
