<?php

use yii\db\Migration;

/**
 * LDAP/AD support + account archiving for `{{%users}}`:
 *  - password becomes nullable (LDAP-provisioned accounts have no local password)
 *  - auth_source: 'local' (built-in) or 'ldap' (provisioned from the directory)
 *  - ldap_dn: the user's distinguished name in the directory
 *  - display_name / email: synced from LDAP attributes
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
                'uuid' => 'varchar(36) PRIMARY KEY',
                'username' => 'varchar(128) NOT NULL UNIQUE',
                'password' => 'varchar(64) NULL',
                'created_time' => 'bigint NOT NULL',
            ]);
            $this->execute('INSERT INTO {{%users_new}} (uuid, username, password, created_time) SELECT uuid, username, password, created_time FROM {{%users}}');
            $this->dropTable('{{%users}}');
            $this->renameTable('{{%users_new}}', '{{%users}}');
        } else {
            $this->alterColumn('{{%users}}', 'password', 'varchar(64) NULL');
        }
        $this->addColumn('{{%users}}', 'auth_source', "varchar(16) NOT NULL DEFAULT 'local'");
        $this->addColumn('{{%users}}', 'ldap_dn', 'varchar(255) NULL');
        $this->addColumn('{{%users}}', 'display_name', 'varchar(128) NULL');
        $this->addColumn('{{%users}}', 'email', 'varchar(255) NULL');
        $this->addColumn('{{%users}}', 'archived', 'tinyint NOT NULL DEFAULT 0');
        $this->addColumn('{{%users}}', 'last_login_time', 'bigint NULL');
    }

    public function safeDown()
    {
        $this->dropColumn('{{%users}}', 'last_login_time');
        $this->dropColumn('{{%users}}', 'archived');
        $this->dropColumn('{{%users}}', 'email');
        $this->dropColumn('{{%users}}', 'display_name');
        $this->dropColumn('{{%users}}', 'ldap_dn');
        $this->dropColumn('{{%users}}', 'auth_source');
        if ($this->db->driverName !== 'sqlite') {
            $this->alterColumn('{{%users}}', 'password', 'varchar(64) NOT NULL');
        }
    }
}
