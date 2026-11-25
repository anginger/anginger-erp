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

namespace anginger\modules\product\controllers;

use anginger\controllers\ResourceController;
use anginger\models\Product;
use yii\db\ActiveRecord;
use anginger\models\User;
use yii\web\Response;
use Yii;

/**
 * JSON API for products, mounted under /api/product (see urlManager rules).
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
            return false;
        }
        return true;
    }


    protected function modelClass(): string
    {
        return Product::class;
    }

    protected function pkParam(): string
    {
        return 'uuid';
    }

    protected function requiredCreateFields(): array
    {
        return ['display_name', 'cost_value', 'sell_value', 'remain_amount'];
    }

    protected function requiredUpdateFields(): array
    {
        return ['uuid', 'display_name', 'cost_value', 'sell_value', 'remain_amount'];
    }

    protected function fillCreate(array $form): ActiveRecord
    {
        $product = new Product();
        $product->uuid = self::uuidV7();
        $this->fillUpdate($product, $form);
        return $product;
    }

    protected function fillUpdate(ActiveRecord $model, array $form): void
    {
        /** @var Product $model */
        $model->display_name = strval($form['display_name']);
        $model->cost_value = intval($form['cost_value']);
        $model->sell_value = intval($form['sell_value']);
        $model->remain_amount = intval($form['remain_amount']);
    }
}
