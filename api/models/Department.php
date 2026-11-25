<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace anginger\models;

use yii\db\ActiveRecord;

/**
 * @property string $dep_id
 * @property string|null $dep_name
 * @property string|null $manager_emp_id
 */
class Department extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%departments}}';
    }

    public function getManager(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Employee::class, ['emp_id' => 'manager_emp_id']);
    }

    public function getEmployees(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Employee::class, ['dep_id' => 'dep_id']);
    }

    public function rules(): array
    {
        return [
            [["dep_id", "dep_name"], "required"],
            [["dep_id", "dep_name"], "string", "max" => 9],
            [["dep_name"], "unique"],
            [["manager_emp_id"], "string", "max" => 36],
        ];
    }
}
