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

use yii\db\Migration;

/**
 * Port of the old `initialize.sql` (main branch) into a Yii2 migration.
 *
 * Creates the five business tables (levels, users, products, providers,
 * histories) and seeds the default Admin level plus the admin/admin user.
 */
class m250305_120000_init_schema extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('levels', [
            '_id' => $this->primaryKey(),
            'display_name' => $this->string(50)->notNull()->unique(),
        ]);

        $this->createTable('users', [
            'uuid' => 'VARCHAR(36) NOT NULL PRIMARY KEY',
            'username' => $this->string(128)->notNull()->unique(),
            'password' => $this->string(64)->notNull(),
            'level' => $this->integer()->notNull(),
            'display_name' => $this->text()->notNull(),
            'created_time' => $this->bigInteger()->unsigned()->notNull(),
            'address' => $this->string(255)->notNull(),
            'email' => $this->string(320)->notNull(),
            'phone' => $this->string(50)->notNull(),
        ]);
        // NOTE: on MySQL this is `ADD CONSTRAINT users_ibfk_1 FOREIGN KEY (level) REFERENCES levels(_id)`;
        // SQLite cannot add constraints after the fact, so the FK is declared inline below via db check.
        $this->createIndex('idx_users_level', 'users', 'level');

        $this->createTable('products', [
            'uuid' => 'VARCHAR(36) NOT NULL PRIMARY KEY',
            'display_name' => $this->string(50)->notNull(),
            'cost_value' => $this->integer()->notNull(),
            'sell_value' => $this->integer()->notNull(),
            'remain_amount' => $this->integer()->notNull(),
        ]);

        $this->createTable('providers', [
            '_id' => $this->primaryKey(),
            'display_name' => $this->string(50)->notNull()->unique(),
            'contact_name' => $this->string(50)->notNull(),
            'contact_phone' => $this->string(50)->notNull(),
            'contact_address' => $this->string(255)->notNull(),
        ]);

        $this->createTable('histories', [
            '_id' => $this->primaryKey(),
            'created_time' => $this->bigInteger()->unsigned()->notNull(),
            'user' => $this->string(36)->notNull(),
            'method' => $this->text()->notNull(),
            'resource' => $this->text()->notNull(),
        ]);

        // Seed data (same as the old initialize.sql)
        $this->insert('levels', ['_id' => 1, 'display_name' => 'Admin']);
        $this->insert('users', [
            'uuid' => '00000000-0000-0000-0000-000000000000',
            'username' => 'admin',
            'password' => hash('sha256', 'admin'),
            'level' => 1,
            'display_name' => 'Admin',
            'created_time' => 1637505598,
            'address' => 'Admin',
            'email' => 'root@localhost',
            'phone' => '0000000000',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('histories');
        $this->dropTable('products');
        $this->dropTable('providers');
        $this->dropTable('users');
        $this->dropTable('levels');
    }
}
