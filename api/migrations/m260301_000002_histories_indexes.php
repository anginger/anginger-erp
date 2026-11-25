<?php

use yii\db\Migration;

/**
 * Indexes for the audit log (it grows unbounded and is listed newest-first).
 */
class m260301_000002_histories_indexes extends Migration
{
    public function safeUp()
    {
        $existing = [];
        foreach ($this->db->schema->getTableIndexes('{{%histories}}') as $idx) {
            $existing[$idx->name] = true;
        }
        if (!isset($existing['idx_histories_created_time'])) {
            $this->createIndex('idx_histories_created_time', '{{%histories}}', 'created_time');
        }
        if (!isset($existing['idx_histories_user'])) {
            $this->createIndex('idx_histories_user', '{{%histories}}', 'user');
        }
    }

    public function safeDown()
    {
        $this->dropIndex('idx_histories_created_time', '{{%histories}}');
        $this->dropIndex('idx_histories_user', '{{%histories}}');
    }
}
