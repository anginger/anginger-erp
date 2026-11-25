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

namespace anginger\modules\history\controllers;

use anginger\controllers\BaseController;
use anginger\models\History;
use anginger\models\User;
use yii\helpers\ArrayHelper;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * Read-only JSON API for the audit log, mounted under /api/history.
 */
class ApiController extends BaseController
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
        if (!is_scalar($id) || $id === '') {
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
        $items = History::find()->orderBy(['created_time' => SORT_DESC])->all();
        $names = [];
        foreach (User::find()->select(['uuid', 'username'])->all() as $u) {
            $names[$u->uuid] = $u->username;
        }
        $rows = array_map(function (History $h) use ($names) {
            $row = ArrayHelper::toArray($h);
            $row['user_name'] = $names[$h->user] ?? null;
            return $row;
        }, $items);
        return $this->asJson($rows);
    }
}
