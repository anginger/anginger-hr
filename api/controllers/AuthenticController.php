<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace anginger\controllers;

use anginger\models\User;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * Session authentication.
 *
 *   GET    /api/authentic/session -> current user (200) or 401
 *   POST   /api/authentic/session -> login, 201 or 401
 *   DELETE /api/authentic/session -> logout, 204 or 401
 */
class AuthenticController extends BaseController
{
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'session' => ['GET', 'POST', 'DELETE'],
                ],
            ],
        ]);
    }

    public function actionSession(): Response
    {
        $request = Yii::$app->request;
        switch ($request->method) {
            case 'GET':
                $identity = Yii::$app->user->identity;
                if ($identity instanceof User) {
                    return $this->asJson($identity);
                }
                return $this->unauthorized();

            case 'POST':
                $form = $request->getBodyParams();
                if (!isset($form['username']) || !isset($form['password'])) {
                    return $this->badRequest();
                }
                $user = User::findByUsernameAndPassword(strval($form['username']), strval($form['password']));
                if ($user === null) {
                    return $this->unauthorized();
                }
                Yii::$app->user->login($user);
                return $this->emptyResponse(201);

            case 'DELETE':
                if (Yii::$app->user->isGuest) {
                    return $this->unauthorized();
                }
                Yii::$app->user->logout();
                return $this->emptyResponse(204);
        }
        return $this->badRequest();
    }
}
