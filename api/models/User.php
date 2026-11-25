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
 * Login account. `uuid` is the owning employee's emp_id for built-in accounts;
 * LDAP-provisioned accounts get a uuidv7 instead.
 *
 * Passwords are stored as sha256 hashes, exactly like the old implementation
 * (keeps the seeded admin/admin account working). LDAP accounts have a NULL
 * password and authenticate against the directory instead.
 *
 * @property string $uuid
 * @property string $username
 * @property string $role admin|user
 * @property string|null $password
 * @property string $auth_source local|ldap
 * @property string|null $ldap_dn
 * @property string|null $display_name
 * @property string|null $email
 * @property int $archived
 * @property int $created_time
 * @property int|null $last_login_time
 */
class User extends ActiveRecord implements IdentityInterface
{
    public static function tableName(): string
    {
        return '{{%users}}';
    }

    public static function findIdentity($id): ?IdentityInterface
    {
        $user = static::findOne($id);
        // archived accounts lose their session immediately
        if ($user !== null && $user->archived) {
            return null;
        }
        return $user;
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
            'password' => \anginger\components\AuthService::hashPassword($password),
        ]);
    }

    /**
     * The password hash must never leak into API responses; expose the
     * owning employee's name as display_name instead.
     */
        public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

public function fields(): array
    {
        $fields = parent::fields();
        unset($fields['password']);
        unset($fields['ldap_dn']);
        $fields['display_name'] = function (User $model) {
            if (!empty($model->display_name)) {
                return $model->display_name;
            }
            $employee = $model->employee;
            return $employee !== null ? $employee->emp_name : null;
        };
        return $fields;
    }

    public function rules(): array
    {
        return [
            [['uuid', 'username'], 'required'],
            [['uuid'], 'string', 'max' => 36],
            [['username'], 'string', 'max' => 128],
            [['username'], 'unique'],
            [['password', 'ldap_dn'], 'string', 'max' => 255],
            [['role'], 'in', 'range' => ['admin', 'user']],
            [['auth_source'], 'in', 'range' => ['local', 'ldap']],
            [['archived'], 'in', 'range' => [0, 1]],
            [['created_time', 'last_login_time'], 'integer'],
            [['display_name'], 'string', 'max' => 128],
            [['email'], 'string', 'max' => 255],
            [['email'], 'email'],
        ];
    }

    public function beforeSave($insert): bool
    {
        if ($insert && empty($this->created_time)) {
            $this->created_time = time();
        }
        return parent::beforeSave($insert);
    }
}
