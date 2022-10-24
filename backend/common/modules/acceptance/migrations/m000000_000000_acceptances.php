<?php

use yii\db\Migration;

class m000000_000000_acceptances extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions
                = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%acceptances}}',
            [
                'id' => $this->primaryKey(11)->unsigned(),
                'accept_token' => $this->string(255)->null(),
                'channel' => $this->string(32)->notNull(),
                'phone' => $this->string(255)->notNull(),
                'code' => $this->string(8)->null(),
                'generate_attempt_count' => $this->integer(11)->notNull(),
                'code_attempt_count' => $this->integer(11)->notNull(),
                'used_at' => $this->timestamp(),
                'retry_at' => $this->timestamp(),
                'expired_at' => $this->timestamp(),
                'created_at' => $this->timestamp(),
                'updated_at' => $this->timestamp(),
            ],
            $tableOptions
        );

        $this->createIndex('IDX_acceptances_accept_token', '{{%acceptances}}',
            ['accept_token']);
        $this->createIndex('IDX_acceptances_channel', '{{%acceptances}}',
            ['channel']);
        $this->createIndex('IDX_acceptances_phone', '{{%acceptances}}',
            ['phone']);
    }

    public function safeDown()
    {
        $this->dropTable('{{%acceptances}}');
    }
}
