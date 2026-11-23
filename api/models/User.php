<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace anginger\models;

use yii\db\ActiveRecord;
use yii\db\Query;

/**
 * @property string $uuid employee ID this account belongs to
 * @property string $username
 * @property string $password SHA-256 hex digest
 * @property int $created_time unix timestamp
 */
class User extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%users}}';
    }

    public function getEmployee(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Employee::class, ['emp_id' => 'uuid']);
    }

    /**
     * Passwords are stored as SHA-256 hex digests (kept for compatibility
     * with the original schema, e.g. the seeded admin/admin account).
     */
    public static function hashPassword(string $password): string
    {
        return hash('sha256', $password);
    }

    public static function findByCredentials(string $username, string $password): ?self
    {
        return static::findOne([
            'username' => $username,
            'password' => static::hashPassword($password),
        ]);
    }

    /**
     * All accounts joined with the owning employee's name.
     *
     * @return array[] rows of uuid, username, display_name, created_time (no password)
     */
    public static function allWithDisplayName(): array
    {
        return (new Query())
            ->select(['users.uuid', 'username', 'created_time', 'emp_name AS display_name'])
            ->from('{{%users}}')
            ->leftJoin('{{%employees}}', 'employees.emp_id = users.uuid')
            ->orderBy(['username' => SORT_ASC])
            ->all();
    }

    /**
     * @return array|null row of uuid, username, display_name, created_time (no password)
     */
    public static function findWithDisplayName(string $uuid): ?array
    {
        $row = (new Query())
            ->select(['users.uuid', 'username', 'created_time', 'emp_name AS display_name'])
            ->from('{{%users}}')
            ->leftJoin('{{%employees}}', 'employees.emp_id = users.uuid')
            ->where(['users.uuid' => $uuid])
            ->one();
        return $row ?: null;
    }
}
