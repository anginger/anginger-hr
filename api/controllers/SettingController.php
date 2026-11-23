<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace anginger\controllers;

use anginger\components\AccessFilter;
use anginger\components\BaseController;
use anginger\components\CurrentUser;
use anginger\models\User;
use Yii;
use yii\web\Response;

class SettingController extends BaseController
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessFilter::class,
            ],
        ]);
    }

    /**
     * GET /setting/ - profile of the currently logged-in user.
     */
    public function actionIndex(): Response
    {
        return $this->asJson(CurrentUser::get());
    }

    /**
     * GET /setting/users - all accounts (passwords are never exposed).
     */
    public function actionAllUsers(): Response
    {
        return $this->asJson(User::allWithDisplayName());
    }

    /**
     * GET /setting/user?uuid=...
     */
    public function actionGetUser(): Response
    {
        $uuid = Yii::$app->getRequest()->getQueryParam('uuid');
        if (empty($uuid)) {
            return $this->respondError(400, 'bad request');
        }
        $user = User::findWithDisplayName($uuid);
        if ($user === null) {
            return $this->respondError(404, 'not found');
        }
        return $this->asJson($user);
    }

    /**
     * POST /setting/user - create an account for an employee, 201.
     */
    public function actionPostUser(): Response
    {
        $form = Yii::$app->getRequest()->post();
        if (empty($form['uuid']) || empty($form['username']) || empty($form['password'])) {
            return $this->respondError(400, 'bad request');
        }
        if (User::findOne(['uuid' => $form['uuid']]) !== null
            || User::findOne(['username' => $form['username']]) !== null) {
            return $this->respondError(400, 'bad request');
        }
        $user = new User();
        $user->uuid = $form['uuid'];
        $user->username = $form['username'];
        $user->password = User::hashPassword($form['password']);
        $user->created_time = time();
        $user->insert();
        return $this->respondEmpty(201);
    }

    /**
     * PUT /setting/user - update an account (password optional), 204.
     */
    public function actionPutUser(): Response
    {
        $form = Yii::$app->getRequest()->post();
        if (empty($form['uuid']) || empty($form['username'])) {
            return $this->respondError(400, 'bad request');
        }
        $user = User::findOne(['uuid' => $form['uuid']]);
        if ($user === null) {
            return $this->respondError(404, 'not found');
        }
        $user->username = $form['username'];
        if (!empty($form['password'])) {
            $user->password = User::hashPassword($form['password']);
        }
        $user->update();
        return $this->respondEmpty(204);
    }

    /**
     * DELETE /setting/user?uuid=... - 204.
     */
    public function actionDeleteUser(): Response
    {
        $uuid = Yii::$app->getRequest()->getQueryParam('uuid');
        if (empty($uuid)) {
            return $this->respondError(400, 'bad request');
        }
        $user = User::findOne(['uuid' => $uuid]);
        if ($user !== null) {
            $user->delete();
        }
        return $this->respondEmpty(204);
    }
}
