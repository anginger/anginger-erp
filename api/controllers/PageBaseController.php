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

use Yii;

/**
 * Base class for module page controllers (server-rendered HTML).
 * Guests are redirected to the login page instead of getting a 403 JSON.
 */
class PageBaseController extends BaseController
{
    public $layout = '@app/views/layouts/main';

    /** Module pages are admin-only unless the controller opts out. */
    public bool $requireAdmin = true;

    public function beforeAction($action): bool
    {
        if (Yii::$app->user->isGuest) {
            $this->logHistory(); // beforeAction returning false skips afterAction
            Yii::$app->response->redirect(['site/login']);
            return false;
        }
        if ($this->requireAdmin) {
            $identity = Yii::$app->user->identity;
            if (!$identity instanceof \anginger\models\User || !$identity->isAdmin()) {
                $this->logHistory();
                throw new \yii\web\ForbiddenHttpException('需要管理員權限');
            }
        }
        return parent::beforeAction($action);
    }
}
