<?php

use yii\db\Migration;

/**
 * Seeds the initial departments, employees and the default admin account
 * (admin / admin, SHA-256 hashed password).
 */
class m260223_000004_seed_hr_data extends Migration
{
    public function safeUp()
    {
        $data = require __DIR__ . '/hr_seed_data.php';
        foreach ($data as $table => $block) {
            $this->batchInsert('{{%' . $table . '}}', $block['columns'], $block['rows']);
        }
    }

    public function safeDown()
    {
        $this->truncateTable('{{%users}}');
        $this->truncateTable('{{%employees}}');
        $this->truncateTable('{{%departments}}');
    }
}
