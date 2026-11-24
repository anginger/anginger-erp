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

namespace anginger\modules\user\controllers;

use anginger\components\LdapClient;
use anginger\controllers\ResourceController;
use anginger\models\User;
use Yii;
use yii\db\ActiveRecord;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * JSON API for users, mounted under /api/user.
 *
 *   POST /api/user/archive    body {uuid, archived} — block/unblock an account
 *
 * While LDAP/AD is enabled, built-in registration (POST create) is disabled —
 * accounts are provisioned from the directory on first login instead.
 */
class ApiController extends ResourceController
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'archive' => ['POST'],
                ],
            ],
        ]);
    }

    /**
     * POST /api/user/archive — archive (block) or unarchive an account.
     * Archived users cannot log in (local password or LDAP) and lose live sessions.
     */
    public function actionArchive(): Response
    {
        if (!$this->requireLogin()) {
            return $this->forbidden();
        }
        $form = Yii::$app->request->getBodyParams();
        $uuid = $form['uuid'] ?? null;
        if ($uuid === null || $uuid === '' || !isset($form['archived'])) {
            return $this->badRequest();
        }
        $user = User::findOne(strval($uuid));
        if ($user === null) {
            return $this->notFound();
        }
        $user->archived = $form['archived'] ? 1 : 0;
        if (!$user->save(false, ['archived'])) {
            return $this->serverError();
        }
        return $this->asJson(['uuid' => $user->uuid, 'archived' => $user->archived]);
    }

    protected function validateCreate(array $form): ?Response
    {
        // LDAP/AD enabled: built-in registration is off, accounts come from the directory
        if (LdapClient::enabled()) {
            Yii::$app->response->setStatusCode(403);
            return $this->asJson(['message' => 'LDAP/AD 登入已啟用，無法手動新增內建帳號（帳號由 LDAP 同步）']);
        }
        return null;
    }

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
