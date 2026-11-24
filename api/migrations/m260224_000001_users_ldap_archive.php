<?php

use yii\db\Migration;

/**
 * LDAP/AD support + account archiving for `users`:
 *  - password becomes nullable (LDAP-provisioned accounts have no local password)
 *  - auth_source: 'local' (built-in) or 'ldap' (provisioned from the directory)
 *  - ldap_dn: the user's distinguished name in the directory
 *  - archived: 1 = blocked (cannot log in via password or LDAP, sessions invalidated)
 *  - last_login_time
 */
class m260224_000001_users_ldap_archive extends Migration
{
    public function safeUp()
    {
        if ($this->db->driverName === 'sqlite') {
            // SQLite cannot ALTER COLUMN — rebuild the table with a nullable password
            $this->createTable('{{%users_new}}', [
                'uuid' => 'VARCHAR(36) NOT NULL PRIMARY KEY',
                'username' => 'VARCHAR(128) NOT NULL UNIQUE',
                'password' => 'VARCHAR(64) NULL',
                'level' => 'INTEGER NOT NULL',
                'display_name' => 'TEXT NOT NULL',
                'created_time' => 'BIGINT NOT NULL',
                'address' => 'VARCHAR(255) NOT NULL',
                'email' => 'VARCHAR(320) NOT NULL',
                'phone' => 'VARCHAR(50) NOT NULL',
            ]);
            $this->execute('INSERT INTO {{%users_new}} (uuid, username, password, level, display_name, created_time, address, email, phone) SELECT uuid, username, password, level, display_name, created_time, address, email, phone FROM {{%users}}');
            $this->dropTable('{{%users}}');
            $this->renameTable('{{%users_new}}', '{{%users}}');
            $this->createIndex('idx_users_level', '{{%users}}', 'level');
        } else {
            $this->alterColumn('{{%users}}', 'password', 'VARCHAR(64) NULL');
        }
        $this->addColumn('{{%users}}', 'auth_source', "VARCHAR(16) NOT NULL DEFAULT 'local'");
        $this->addColumn('{{%users}}', 'ldap_dn', 'VARCHAR(255) NULL');
        $this->addColumn('{{%users}}', 'archived', 'TINYINT NOT NULL DEFAULT 0');
        $this->addColumn('{{%users}}', 'last_login_time', 'BIGINT NULL');
    }

    public function safeDown()
    {
        $this->dropColumn('{{%users}}', 'last_login_time');
        $this->dropColumn('{{%users}}', 'archived');
        $this->dropColumn('{{%users}}', 'ldap_dn');
        $this->dropColumn('{{%users}}', 'auth_source');
        if ($this->db->driverName !== 'sqlite') {
            $this->alterColumn('{{%users}}', 'password', 'VARCHAR(64) NOT NULL');
        }
    }
}
