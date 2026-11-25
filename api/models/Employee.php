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

    public function rules(): array
    {
        return [
            [["emp_id"], "required"],
            [["emp_id"], "string", "max" => 36],
            [["emp_name"], "string", "max" => 8],
            [["job_title"], "string", "max" => 11],
            [["dep_id"], "string", "max" => 9],
            [["city"], "string", "max" => 7],
            [["address"], "string", "max" => 32],
            [["phone"], "string", "max" => 16],
            [["zip_code"], "string", "max" => 3],
            [["month_salary", "annual_leave"], "number"],
        ];
    }
}
