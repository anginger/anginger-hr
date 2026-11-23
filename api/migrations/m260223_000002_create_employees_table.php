<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%employees}}`.
 */
class m260223_000002_create_employees_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%employees}}', [
            'emp_id' => 'varchar(36) PRIMARY KEY',
            'emp_name' => 'varchar(8) NULL',
            'job_title' => 'varchar(11) NULL',
            'dep_id' => 'varchar(9) NULL',
            'city' => 'varchar(7) NULL',
            'address' => 'varchar(32) NULL',
            'phone' => 'varchar(16) NULL',
            'zip_code' => 'varchar(3) NULL',
            'month_salary' => 'double NULL',
            'annual_leave' => 'double NULL',
        ]);
        $this->createIndex('idx_employees_dep_id', '{{%employees}}', 'dep_id');
    }

    public function safeDown()
    {
        $this->dropTable('{{%employees}}');
    }
}
