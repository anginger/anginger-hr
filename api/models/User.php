<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace anginger\models;

use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * Login account. `uuid` is the owning employee's emp_id.
 *
 * Passwords are stored as sha256 hashes, exactly like the old implementation
 * (keeps the seeded admin/admin account working).
 *
 * @property string $uuid
 * @property string $username
 * @property string $password
 * @property int $created_time
 */
class User extends ActiveRecord implements IdentityInterface
{
    public static function tableName(): string
    {
        return '{{%users}}';
    }

    public static function findIdentity($id): ?IdentityInterface
    {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null): ?IdentityInterface
    {
        // Session-based authentication only, no bearer tokens.
        return null;
    }

    public function getId(): string
    {
        return $this->uuid;
    }

    public function getAuthKey(): string
    {
        return hash('sha256', $this->uuid . $this->password);
    }

    public function validateAuthKey($authKey): bool
    {
        return $this->getAuthKey() === $authKey;
    }

    public function getEmployee(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Employee::class, ['emp_id' => 'uuid']);
    }

    /**
     * Finds a user by username and plain-text password.
     */
    public static function findByUsernameAndPassword(string $username, string $password): ?User
    {
        return static::findOne([
            'username' => $username,
            'password' => hash('sha256', $password),
        ]);
    }

    /**
     * The password hash must never leak into API responses; expose the
     * owning employee's name as display_name instead.
     */
    public function fields(): array
    {
        $fields = parent::fields();
        unset($fields['password']);
        $fields['display_name'] = function (User $model) {
            $employee = $model->employee;
            return $employee !== null ? $employee->emp_name : null;
        };
        return $fields;
    }

    public function beforeSave($insert): bool
    {
        if ($insert && empty($this->created_time)) {
            $this->created_time = time();
        }
        return parent::beforeSave($insert);
    }
}
