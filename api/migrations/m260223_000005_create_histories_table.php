<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%histories}}` (request audit log).
 */
class m260223_000005_create_histories_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%histories}}', [
            'id' => $this->primaryKey(),
            'created_time' => $this->bigInteger()->notNull(),
            'user' => $this->string(64)->notNull(),
            'method' => $this->string(10)->notNull(),
            'resource' => $this->text()->notNull(),
        ]);
        $this->createIndex('idx_histories_created_time', '{{%histories}}', 'created_time');
    }

    public function safeDown()
    {
        $this->dropTable('{{%histories}}');
    }
}
