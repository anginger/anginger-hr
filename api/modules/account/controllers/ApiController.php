<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace anginger\modules\account\controllers;

use anginger\components\LdapClient;
use anginger\controllers\ResourceController;
use anginger\models\Employee;
use anginger\models\User;
use Yii;
use yii\db\ActiveRecord;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * JSON API for login accounts (users.uuid = employees.emp_id):
 *   GET    /api/account?uuid=...   POST /api/account   PUT /api/account   DELETE /api/account?uuid=...
 *   GET    /api/account/all
 *   POST   /api/account/archive    body {uuid, archived} — block/unblock an account
 *
 * While LDAP/AD is enabled, built-in registration (POST create) is disabled —
 * accounts are provisioned from the directory on first login instead.
 */
class ApiController extends ResourceController
{
    public function beforeAction($action): bool
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        $identity = Yii::$app->user->identity;
        if (!$identity instanceof User || !$identity->isAdmin()) {
            Yii::$app->response->setStatusCode(403);
            Yii::$app->response->format = Response::FORMAT_JSON;
            Yii::$app->response->data = ['message' => 'forbidden'];
            $this->logHistory();
            return false;
        }
        return true;
    }

    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'archive' => ['POST'],
                ],
            ],
        ]);
    }

    /**
     * POST /api/account/archive — archive (block) or unarchive an account.
     * Archived users cannot log in (local password or LDAP) and lose live sessions.
     */
    public function actionArchive(): Response
    {
        if (!$this->requireLogin()) {
            return $this->forbidden();
        }
        $form = Yii::$app->request->getBodyParams();
        $uuid = $form['uuid'] ?? null;
        if (!is_scalar($uuid) || $uuid === '' || !isset($form['archived'])) {
            return $this->badRequest();
        }
        $user = User::findOne(strval($uuid));
        if ($user === null) {
            return $this->notFound();
        }
        $user->archived = $form['archived'] ? 1 : 0;
        if (!$user->save(false, ['archived'])) {
            return $this->serverError();
        }
        return $this->asJson(['uuid' => $user->uuid, 'archived' => $user->archived]);
    }

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
        // LDAP/AD enabled: built-in registration is off, accounts come from the directory
        if (LdapClient::enabled()) {
            Yii::$app->response->setStatusCode(403);
            return $this->asJson(['message' => 'LDAP/AD 登入已啟用，無法手動新增內建帳號（帳號由 LDAP 同步）']);
        }
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
        $user->password = \anginger\components\AuthService::hashPassword(strval($form['password']));
        $user->created_time = time();
        return $user;
    }

    protected function fillUpdate(ActiveRecord $model, array $form): void
    {
        /** @var User $model */
        $model->username = strval($form['username']);
        if (!empty($form['password'])) {
            $model->password = \anginger\components\AuthService::hashPassword(strval($form['password']));
        }
    }

    public function actionAll(): Response
    {
        if (!$this->requireLogin()) {
            return $this->forbidden();
        }
        // User::fields() hides the password hash and adds display_name
        return $this->asJson(User::find()->with('employee')->orderBy(['username' => SORT_ASC])->all());
    }
}
