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

namespace anginger\models;

use yii\db\ActiveRecord;

/**
 * Product model.
 *
 * Port of `Slim\Models\Product` (main branch).
 *
 * @property string $uuid
 * @property string $display_name
 * @property int $cost_value
 * @property int $sell_value
 * @property int $remain_amount
 */
class Product extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'products';
    }

    public function rules(): array
    {
        return [
            [['uuid', 'display_name', 'cost_value', 'sell_value', 'remain_amount'], 'required'],
            [['uuid'], 'string', 'max' => 36],
            [['display_name'], 'string', 'max' => 50],
            [['cost_value', 'sell_value', 'remain_amount'], 'integer'],
        ];
    }
}
