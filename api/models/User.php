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
use yii\web\IdentityInterface;

/**
 * User model.
 *
 * Port of `Slim\Models\User` (main branch). Passwords are stored as
 * sha256 hashes, exactly like the old implementation.
 *
 * @property string $uuid
 * @property string $username
 * @property string|null $password
 * @property int $level
 * @property string $display_name
 * @property int $created_time
 * @property string $address
 * @property string $email
 * @property string $phone
 * @property string $auth_source local|ldap
 * @property string|null $ldap_dn
 * @property int $archived
 * @property int|null $last_login_time
 */
class User extends ActiveRecord implements IdentityInterface
{
    public static function tableName(): string
    {
        return 'users';
    }

    public static function findIdentity($id): ?IdentityInterface
    {
        $user = static::findOne($id);
        // archived accounts lose their session immediately
        if ($user !== null && $user->archived) {
            return null;
        }
        return $user;
    }

    public static function findIdentityByAccessToken($token, $type = null): ?IdentityInterface
    {
        // Session-based authentication only, no bearer tokens.
        return null;
    }

    public function getId(): string
    {
        return $this->uuid;
    }

    public function getAuthKey(): string
    {
        return hash('sha256', $this->uuid . $this->password);
    }

    public function validateAuthKey($authKey): bool
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * The password hash must never leak into API responses
     * (same as the old `jsonSerialize()` which unset `password`).
     */
        public function isAdmin(): bool
    {
        $level = Level::findOne($this->level);
        return $level !== null && strcasecmp(strval($level->display_name), 'Admin') === 0;
    }

public function fields(): array
    {
        $fields = parent::fields();
        unset($fields['password']);
        unset($fields['ldap_dn']);
        return $fields;
    }

    public function beforeSave($insert): bool
    {
        if ($insert && empty($this->created_time)) {
            $this->created_time = time();
        }
        return parent::beforeSave($insert);
    }

    public function rules(): array
    {
        return [
            [['uuid', 'username', 'level', 'display_name', 'created_time'], 'required'],
            [['uuid'], 'string', 'max' => 36],
            [['username'], 'string', 'max' => 128],
            [['username'], 'unique'],
            [['password', 'ldap_dn'], 'string', 'max' => 255],
            [['level'], 'integer'],
            [['display_name'], 'string'],
            [['address'], 'string', 'max' => 255],
            [['email'], 'string', 'max' => 320],
            [['email'], 'email'],
            [['phone'], 'string', 'max' => 50],
            [['auth_source'], 'in', 'range' => ['local', 'ldap']],
            [['archived'], 'in', 'range' => [0, 1]],
            [['created_time', 'last_login_time'], 'integer'],
        ];
    }
}
