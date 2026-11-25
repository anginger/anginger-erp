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
use anginger\models\User;
use yii\helpers\ArrayHelper;
use Yii;
use yii\web\Response;

/**
 * Server-rendered pages: overview, login, logout, error.
 * The JSON API still lives in AuthenticController + the resource modules.
 */
class SiteController extends BaseController
{
    public $layout = '@app/views/layouts/main';

        /**
     * Unauthenticated health check for Docker / Render.
     */
    public function actionHealth(): Response
    {
        try {
            Yii::$app->db->createCommand('SELECT 1')->queryScalar();
        } catch (\Throwable $e) {
            Yii::$app->response->setStatusCode(503);
            return $this->asJson(['status' => 'error']);
        }
        return $this->asJson(['status' => 'ok']);
    }

public function actionIndex(): string|Response
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }
        $items = History::find()->orderBy(['created_time' => SORT_DESC])->limit(100)->all();
        $names = [];
        foreach (User::find()->select(['uuid', 'username'])->all() as $u) {
            $names[$u->uuid] = $u->username;
        }
        $histories = array_map(function (History $h) use ($names) {
            $row = ArrayHelper::toArray($h);
            $row['user_name'] = $names[$h->user] ?? null;
            return $row;
        }, $items);
        return $this->render('index', ['histories' => $histories]);
    }

    public function actionLogin(): string|Response
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['site/index']);
        }
        $this->layout = '@app/views/layouts/blank';
        return $this->render('login');
    }

    public function actionLogout(): Response
    {
        $identity = Yii::$app->user->identity;
        if ($identity instanceof User) {
            Yii::$app->params['history_user'] = $identity->uuid; // keep attribution after logout
        }
        Yii::$app->user->logout();
        return $this->redirect(['site/login']);
    }

    public function actionError(): string
    {
        $this->layout = '@app/views/layouts/blank';
        $exception = Yii::$app->errorHandler->exception;
        if ($exception !== null && method_exists($exception, 'getStatusCode')) {
            Yii::$app->response->setStatusCode($exception->getStatusCode());
        }
        return $this->render('error', ['exception' => $exception ?? new \Exception('unknown error')]);
    }
}
