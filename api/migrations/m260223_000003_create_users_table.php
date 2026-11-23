<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%users}}`.
 */
class m260223_000003_create_users_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%users}}', [
            'uuid' => 'varchar(36) PRIMARY KEY',
            'username' => 'varchar(128) NOT NULL UNIQUE',
            'password' => 'varchar(64) NOT NULL',
            'created_time' => 'bigint NOT NULL',
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%users}}');
    }
}
