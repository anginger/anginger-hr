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

class AuthenticController extends BaseController
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessFilter::class,
                'exceptActions' => ['post-session'],
            ],
        ]);
    }

    /**
     * GET /authentic/session - return the currently logged-in user (200) or 401.
     */
    public function actionGetSession(): Response
    {
        return $this->asJson(CurrentUser::get());
    }

    /**
     * POST /authentic/session - log in with username & password, 201 on success.
     */
    public function actionPostSession(): Response
    {
        $request = Yii::$app->getRequest();
        $username = $request->post('username');
        $password = $request->post('password');
        if ($username === null || $password === null) {
            return $this->respondError(400, 'bad request');
        }
        $user = User::findByCredentials($username, $password);
        if ($user === null) {
            return $this->respondError(401, 'unauthorized');
        }
        Yii::$app->getSession()->set('user_id', $user->uuid);
        return $this->respondEmpty(201);
    }

    /**
     * DELETE /authentic/session - log out, 204.
     */
    public function actionDeleteSession(): Response
    {
        Yii::$app->getSession()->remove('user_id');
        return $this->respondEmpty(204);
    }
}
