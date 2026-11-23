<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace anginger\modules\department\controllers;

use anginger\controllers\ResourceController;
use anginger\models\Department;
use anginger\models\Employee;
use yii\db\ActiveRecord;
use yii\web\Response;

/**
 * JSON API for departments:
 *   GET    /api/department?dep_id=...   POST /api/department   PUT /api/department   DELETE /api/department?dep_id=...
 *   GET    /api/department/all
 */
class ApiController extends ResourceController
{
    protected function modelClass(): string
    {
        return Department::class;
    }

    protected function pkParam(): string
    {
        return 'dep_id';
    }

    protected function requiredCreateFields(): array
    {
        return ['dep_name', 'manager_emp_id'];
    }

    protected function requiredUpdateFields(): array
    {
        return ['dep_id', 'dep_name', 'manager_emp_id'];
    }

    protected function validateCreate(array $form): ?Response
    {
        return $this->validateManager($form);
    }

    protected function validateUpdate(ActiveRecord $model, array $form): ?Response
    {
        return $this->validateManager($form);
    }

    private function validateManager(array $form): ?Response
    {
        if (Employee::findOne(['emp_id' => $form['manager_emp_id'] ?? null]) === null) {
            return $this->badRequest();
        }
        return null;
    }

    protected function beforeDelete(ActiveRecord $model): ?Response
    {
        /** @var Department $model */
        // a department that still has employees cannot be deleted
        if (Employee::find()->where(['dep_id' => $model->dep_id])->exists()) {
            return $this->badRequest();
        }
        return null;
    }

    protected function fillCreate(array $form): ActiveRecord
    {
        $department = new Department();
        // short human-readable code, fits the varchar(9) column (e.g. A01)
        $department->dep_id = strtoupper(bin2hex(random_bytes(4)));
        $department->dep_name = strval($form['dep_name']);
        $department->manager_emp_id = strval($form['manager_emp_id']);
        return $department;
    }

    protected function fillUpdate(ActiveRecord $model, array $form): void
    {
        /** @var Department $model */
        $model->dep_name = strval($form['dep_name']);
        $model->manager_emp_id = strval($form['manager_emp_id']);
    }

    public function actionAll(): Response
    {
        if (!$this->requireLogin()) {
            return $this->forbidden();
        }
        $rows = Department::find()->orderBy(['dep_name' => SORT_ASC])->all();
        // attach the manager's name for display
        $result = array_map(function (Department $department) {
            $data = $department->toArray();
            $manager = $department->manager;
            $data['manager_name'] = $manager !== null ? $manager->emp_name : null;
            return $data;
        }, $rows);
        return $this->asJson($result);
    }
}
