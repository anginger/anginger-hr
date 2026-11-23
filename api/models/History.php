<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace anginger\models;

use yii\db\ActiveRecord;

/**
 * Audit log: one row per handled request (see BaseController::logHistory).
 *
 * @property int $id
 * @property int $created_time
 * @property string $user user uuid, or the client IP for guests
 * @property string $method
 * @property string $resource
 */
class History extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%histories}}';
    }
}
