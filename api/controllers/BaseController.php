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
use Yii;
use yii\filters\Cors;
use yii\web\Controller;
use yii\web\Response;

/**
 * Base API controller.
 *
 * Replaces the old Slim middlewares registered on every channel:
 *  - CORS            -> yii\filters\Cors (behaviors, also handles OPTIONS preflight)
 *  - Access          -> Yii::$app->user (session identity), see requireLogin()
 *  - Logger          -> afterAction() writes one row into `histories` per request
 */
class BaseController extends Controller
{
    public function behaviors(): array
    {
        return [
            'cors' => [
                'class' => Cors::class,
                'cors' => [
                    // wildcard '*' is rejected together with credentials, so list
                    // the dev origins explicitly (old config: CORS_DOMAIN=http://localhost:8080)
                    'Origin' => ['http://localhost:8080', 'http://127.0.0.1:8080'],
                    'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
                    'Access-Control-Request-Headers' => ['*'],
                    'Access-Control-Allow-Credentials' => true,
                    'Access-Control-Max-Age' => 3600,
                ],
            ],
        ];
    }

    public function afterAction($action, $result): mixed
    {
        $this->logHistory();
        return parent::afterAction($action, $result);
    }

    /**
     * Port of the old Logger middleware: record every request into `histories`.
     * Authenticated requests are attributed to the user uuid, others to the IP.
     */
    protected function logHistory(): void
    {
        try {
            $identity = Yii::$app->user->identity;
            $history = new History();
            $history->created_time = time();
            $history->user = Yii::$app->params['history_user'] ?? ($identity instanceof User ? $identity->uuid : (string)Yii::$app->request->userIP);
            $history->method = Yii::$app->request->method;
            $history->resource = Yii::$app->request->url;
            $history->save(false);
        } catch (\Throwable $e) {
            Yii::error('Failed to write history: ' . $e->getMessage());
        }
    }

    /**
     * Port of the old Access middleware check used at the top of every
     * resource handler: a logged-in user is required, otherwise 403.
     */
    protected function requireLogin(): bool
    {
        if (!Yii::$app->user->isGuest) {
            return true;
        }
        Yii::$app->response->setStatusCode(403);
        return false;
    }

    /**
     * A logged-in admin is required, otherwise 403.
     */
    protected function requireAdmin(): bool
    {
        $identity = Yii::$app->user->identity;
        if ($identity instanceof User && $identity->isAdmin()) {
            return true;
        }
        Yii::$app->response->setStatusCode(403);
        return false;
    }

    protected function forbidden(): Response
    {
        Yii::$app->response->setStatusCode(403);
        return $this->asJson(['message' => 'forbidden']);
    }

    protected function badRequest(): Response
    {
        Yii::$app->response->setStatusCode(400);
        return $this->asJson(['message' => 'bad request']);
    }

    protected function unauthorized(): Response
    {
        Yii::$app->response->setStatusCode(401);
        return $this->asJson(['message' => 'unauthorized']);
    }

    protected function notFound(): Response
    {
        Yii::$app->response->setStatusCode(404);
        return $this->asJson(['message' => 'not found']);
    }

    protected function conflict(): Response
    {
        Yii::$app->response->setStatusCode(409);
        return $this->asJson(['message' => 'conflict']);
    }

    protected function serverError(): Response
    {
        Yii::$app->response->setStatusCode(500);
        return $this->asJson(['message' => 'internal server error']);
    }

    /**
     * 201/204 responses carry an empty body, like the old Response::send().
     */
    protected function emptyResponse(int $status): Response
    {
        $response = Yii::$app->response;
        $response->setStatusCode($status);
        $response->content = '';
        return $response;
    }
}
