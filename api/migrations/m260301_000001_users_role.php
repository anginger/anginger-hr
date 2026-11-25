<?php

use yii\db\Migration;

/**
 * Adds users.role ('admin' | 'user') for RBAC. The seeded admin account becomes admin.
 */
class m260301_000001_users_role extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%users}}', 'role', "varchar(16) NOT NULL DEFAULT 'user'");
        $this->update('{{%users}}', ['role' => 'admin'], ['username' => 'admin']);
    }

    public function safeDown()
    {
        $this->dropColumn('{{%users}}', 'role');
    }
}
