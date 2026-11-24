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

namespace anginger\modules\history\controllers;

use anginger\controllers\PageBaseController;
use anginger\models\History;
use anginger\models\User;
use yii\helpers\ArrayHelper;

/**
 * Server-rendered audit log page (/history).
 */
class PageController extends PageBaseController
{
    public function actionIndex(): string
    {
        $items = History::find()->orderBy(['created_time' => SORT_DESC])->limit(200)->all();
        $names = [];
        foreach (User::find()->select(['uuid', 'username'])->all() as $u) {
            $names[$u->uuid] = $u->username;
        }
        $rows = array_map(function (History $h) use ($names) {
            $row = ArrayHelper::toArray($h);
            $row['user_name'] = $names[$h->user] ?? null;
            return $row;
        }, $items);
        return $this->render('index', ['items' => $rows]);
    }
}
