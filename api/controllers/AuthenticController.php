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
use Yii;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * Port of `Slim\Controllers\Authentic` (main branch).
 *
 *   GET    /authentic/session -> getSession    (current user or 401)
 *   POST   /authentic/session -> postSession   (login, 201 or 401)
 *   DELETE /authentic/session -> deleteSession (logout, 204 or 401)
 */
class AuthenticController extends BaseController
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'session' => ['GET', 'POST', 'DELETE'],
                ],
            ],
        ]);
    }

    public function actionSession(): Response
    {
        $request = Yii::$app->request;
        switch ($request->method) {
            case 'GET':
                $identity = Yii::$app->user->identity;
                if ($identity instanceof User) {
                    return $this->asJson($identity);
                }
                return $this->unauthorized();

            case 'POST':
                $form = $request->getBodyParams();
                if (!isset($form['username']) || !isset($form['password'])) {
                    return $this->badRequest();
                }
                $user = User::findByUsernameAndPassword(strval($form['username']), strval($form['password']));
                if ($user === null) {
                    return $this->unauthorized();
                }
                Yii::$app->user->login($user);
                return $this->emptyResponse(201);

            case 'DELETE':
                if (Yii::$app->user->isGuest) {
                    return $this->unauthorized();
                }
                Yii::$app->user->logout();
                return $this->emptyResponse(204);
        }
        return $this->badRequest();
    }
}
