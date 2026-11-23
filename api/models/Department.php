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

    /**
     * Serialize the department together with its manager employee
     * (including the manager's own department), as the SPA expects.
     */
    public function toArrayWithManager(): array
    {
        $data = $this->toArray();
        $manager = null;
        if ($this->manager_emp_id !== null) {
            $employee = Employee::findOne(['emp_id' => $this->manager_emp_id]);
            if ($employee !== null) {
                $manager = $employee->toArrayWithDepartment();
            }
        }
        $data['manager'] = $manager;
        return $data;
    }
}
