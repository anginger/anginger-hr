<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace anginger\models;

use yii\db\ActiveRecord;

/**
 * @property string $emp_id
 * @property string|null $emp_name
 * @property string|null $job_title
 * @property string|null $dep_id
 * @property string|null $city
 * @property string|null $address
 * @property string|null $phone
 * @property string|null $zip_code
 * @property double|null $month_salary
 * @property double|null $annual_leave
 */
class Employee extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%employees}}';
    }

    public function getDepartment(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Department::class, ['dep_id' => 'dep_id']);
    }
}
