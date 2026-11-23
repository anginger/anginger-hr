<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%departments}}`.
 */
class m260223_000001_create_departments_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%departments}}', [
            'dep_id' => 'varchar(9) PRIMARY KEY',
            'dep_name' => 'varchar(9) NOT NULL UNIQUE',
            'manager_emp_id' => 'varchar(36) NULL',
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%departments}}');
    }
}
