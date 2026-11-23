<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace anginger\controllers;

use Yii;

/**
 * Base class for module page controllers (server-rendered HTML).
 * Guests are redirected to the login page instead of getting a 403 JSON.
 */
class PageBaseController extends BaseController
{
    public $layout = '@app/views/layouts/main';

    public function beforeAction($action): bool
    {
        if (Yii::$app->user->isGuest) {
            Yii::$app->response->redirect(['site/login']);
            return false;
        }
        return parent::beforeAction($action);
    }
}
