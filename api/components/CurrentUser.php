<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace anginger\components;

use Yii;
use yii\db\Query;

/**
 * Resolves the currently logged-in user from the session.
 */
class CurrentUser
{
    /**
     * @return array|null user row (uuid, username, display_name, created_time) or null when logged out
     */
    public static function get(): ?array
    {
        $uuid = Yii::$app->getSession()->get('user_id');
        if (empty($uuid)) {
            return null;
        }
        $row = (new Query())
            ->select(['users.uuid', 'username', 'created_time', 'emp_name AS display_name'])
            ->from('users')
            ->leftJoin('employees', 'employees.emp_id = users.uuid')
            ->where(['users.uuid' => $uuid])
            ->one();
        return $row ?: null;
    }
}
