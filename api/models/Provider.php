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
 * Provider model.
 *
 * Port of `Slim\Models\Provider` (main branch).
 *
 * @property int $_id
 * @property string $display_name
 * @property string $contact_name
 * @property string $contact_phone
 * @property string $contact_address
 */
class Provider extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'providers';
    }

    public function rules(): array
    {
        return [
            [['display_name', 'contact_name', 'contact_phone', 'contact_address'], 'required'],
            [['display_name'], 'string', 'max' => 50],
            [['display_name'], 'unique'],
            [['contact_name', 'contact_phone'], 'string', 'max' => 50],
            [['contact_address'], 'string', 'max' => 255],
        ];
    }
}
