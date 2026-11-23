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

use Ramsey\Uuid\Uuid;
use Yii;
use yii\db\ActiveRecord;
use yii\db\IntegrityException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * Generic CRUD resource controller.
 *
 * Maps the old Slim routes
 *   GET    /<resource>        -> getOne   (primary key in the query string)
 *   POST   /<resource>        -> postOne  (create, body params)
 *   PUT    /<resource>        -> putOne   (update, body params)
 *   DELETE /<resource>        -> deleteOne(primary key in the query string)
 *   GET    /<resource>/all    -> getAll
 * onto Yii2 actions `index` and `all`.
 */
abstract class ResourceController extends BaseController
{
    /** @return class-string<ActiveRecord> */
    abstract protected function modelClass(): string;

    /** Name of the query/body parameter that carries the primary key (e.g. "uuid" or "id"). */
    abstract protected function pkParam(): string;

    /** @return string[] fields required in the POST body */
    abstract protected function requiredCreateFields(): array;

    /** @return string[] fields required in the PUT body (includes the pk param) */
    abstract protected function requiredUpdateFields(): array;

    abstract protected function fillCreate(array $form): ActiveRecord;

    abstract protected function fillUpdate(ActiveRecord $model, array $form): void;

    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index' => ['GET', 'POST', 'PUT', 'DELETE'],
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
        $request = Yii::$app->request;
        $modelClass = $this->modelClass();
        switch ($request->method) {
            case 'GET':
                $key = $request->getQueryParam($this->pkParam());
                if ($key === null || $key === '') {
                    return $this->badRequest();
                }
                $model = $modelClass::findOne($key);
                return $model !== null ? $this->asJson($model) : $this->notFound();

            case 'POST':
                $form = $request->getBodyParams();
                foreach ($this->requiredCreateFields() as $field) {
                    if (!isset($form[$field])) {
                        return $this->badRequest();
                    }
                }
                $model = $this->fillCreate($form);
                try {
                    return $model->save(false) ? $this->emptyResponse(201) : $this->serverError();
                } catch (IntegrityException) {
                    return $this->conflict();
                }

            case 'PUT':
                $form = $request->getBodyParams();
                foreach ($this->requiredUpdateFields() as $field) {
                    if (!isset($form[$field])) {
                        return $this->badRequest();
                    }
                }
                $model = $modelClass::findOne($form[$this->pkParam()]);
                if ($model === null) {
                    return $this->notFound();
                }
                $this->fillUpdate($model, $form);
                try {
                    return $model->save(false) ? $this->emptyResponse(204) : $this->serverError();
                } catch (IntegrityException) {
                    return $this->conflict();
                }

            case 'DELETE':
                $key = $request->getQueryParam($this->pkParam());
                if ($key === null || $key === '') {
                    return $this->badRequest();
                }
                $model = $modelClass::findOne($key);
                if ($model !== null) {
                    $model->delete();
                }
                return $this->emptyResponse(204);
        }
        return $this->badRequest();
    }

    public function actionAll(): Response
    {
        if (!$this->requireLogin()) {
            return $this->forbidden();
        }
        $modelClass = $this->modelClass();
        // same ordering as the old Model::batch(): display_name DESC
        return $this->asJson($modelClass::find()->orderBy(['display_name' => SORT_DESC])->all());
    }

    /**
     * UUID v7 (RFC 9562) via ramsey/uuid: time-ordered, so uuid primary keys
     * insert sequentially into the B-tree instead of randomly (v4).
     *
     * Replaces Database::guidV4() from the main branch.
     */
    protected static function uuidV7(): string
    {
        return Uuid::uuid7()->toString();
    }
}
