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
 * History model (audit log written by the old Logger middleware).
 *
 * Port of `Slim\Models\History` (main branch).
 *
 * @property int $_id
 * @property int $created_time
 * @property string $user
 * @property string $method
 * @property string $resource
 */
class History extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'histories';
    }

    public function beforeSave($insert): bool
    {
        if ($insert && empty($this->created_time)) {
            $this->created_time = time();
        }
        return parent::beforeSave($insert);
    }
}
