<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace anginger\modules\employee\controllers;

use anginger\controllers\ResourceController;
use anginger\models\Department;
use anginger\models\Employee;
use anginger\models\User;
use Yii;
use yii\db\ActiveRecord;
use yii\web\Response;

/**
 * JSON API for employees:
 *   GET    /api/employee?emp_id=...   POST /api/employee   PUT /api/employee   DELETE /api/employee?emp_id=...
 *   GET    /api/employee/all
 */
class ApiController extends ResourceController
{
    private const FIELDS = [
        'emp_name', 'job_title', 'dep_id', 'city', 'address',
        'phone', 'zip_code', 'month_salary', 'annual_leave',
    ];

    protected function modelClass(): string
    {
        return Employee::class;
    }

    protected function pkParam(): string
    {
        return 'emp_id';
    }

    protected function requiredCreateFields(): array
    {
        return self::FIELDS;
    }

    protected function requiredUpdateFields(): array
    {
        return array_merge(['emp_id'], self::FIELDS);
    }

    protected function validateCreate(array $form): ?Response
    {
        return $this->validateDepartment($form);
    }

    protected function validateUpdate(ActiveRecord $model, array $form): ?Response
    {
        return $this->validateDepartment($form);
    }

    private function validateDepartment(array $form): ?Response
    {
        if (Department::findOne(['dep_id' => $form['dep_id'] ?? null]) === null) {
            return $this->badRequest();
        }
        return null;
    }

    protected function beforeDelete(ActiveRecord $model): ?Response
    {
        /** @var Employee $model */
        // an employee that owns a login account cannot be deleted
        if (User::findOne(['uuid' => $model->emp_id]) !== null) {
            return $this->badRequest();
        }
        return null;
    }

    protected function fillCreate(array $form): ActiveRecord
    {
        $employee = new Employee();
        // UUIDv7: time-ordered primary key, index-friendly
        $employee->emp_id = self::uuidV7();
        $this->fill($employee, $form);
        return $employee;
    }

    protected function fillUpdate(ActiveRecord $model, array $form): void
    {
        $this->fill($model, $form);
    }

    private function fill(Employee $employee, array $form): void
    {
        $employee->emp_name = strval($form['emp_name']);
        $employee->job_title = strval($form['job_title']);
        $employee->dep_id = strval($form['dep_id']);
        $employee->city = strval($form['city']);
        $employee->address = strval($form['address']);
        $employee->phone = strval($form['phone']);
        $employee->zip_code = strval($form['zip_code']);
        $employee->month_salary = floatval($form['month_salary']);
        $employee->annual_leave = floatval($form['annual_leave']);
    }

    public function actionAll(): Response
    {
        if (!$this->requireLogin()) {
            return $this->forbidden();
        }
        return $this->asJson(Employee::find()->orderBy(['emp_name' => SORT_ASC])->all());
    }
}
