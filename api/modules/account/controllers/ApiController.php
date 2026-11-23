<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace anginger\modules\account\controllers;

use anginger\controllers\ResourceController;
use anginger\models\Employee;
use anginger\models\User;
use yii\db\ActiveRecord;
use yii\web\Response;

/**
 * JSON API for login accounts (users.uuid = employees.emp_id):
 *   GET    /api/account?uuid=...   POST /api/account   PUT /api/account   DELETE /api/account?uuid=...
 *   GET    /api/account/all
 */
class ApiController extends ResourceController
{
    protected function modelClass(): string
    {
        return User::class;
    }

    protected function pkParam(): string
    {
        return 'uuid';
    }

    protected function requiredCreateFields(): array
    {
        return ['uuid', 'username', 'password'];
    }

    protected function requiredUpdateFields(): array
    {
        // password is optional on update: when empty the old hash is kept
        return ['uuid', 'username'];
    }

    protected function validateCreate(array $form): ?Response
    {
        // the account must belong to an existing employee
        if (Employee::findOne(['emp_id' => $form['uuid'] ?? null]) === null) {
            return $this->badRequest();
        }
        return null;
    }

    protected function fillCreate(array $form): ActiveRecord
    {
        $user = new User();
        $user->uuid = strval($form['uuid']);
        $user->username = strval($form['username']);
        $user->password = hash('sha256', strval($form['password']));
        $user->created_time = time();
        return $user;
    }

    protected function fillUpdate(ActiveRecord $model, array $form): void
    {
        /** @var User $model */
        $model->username = strval($form['username']);
        if (!empty($form['password'])) {
            $model->password = hash('sha256', strval($form['password']));
        }
    }

    public function actionAll(): Response
    {
        if (!$this->requireLogin()) {
            return $this->forbidden();
        }
        // User::fields() hides the password hash and adds display_name
        return $this->asJson(User::find()->orderBy(['username' => SORT_ASC])->all());
    }
}
