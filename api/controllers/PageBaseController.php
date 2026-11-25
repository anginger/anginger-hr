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

    /** Module pages are admin-only unless the controller opts out. */
    public bool $requireAdmin = true;

    public function beforeAction($action): bool
    {
        if (Yii::$app->user->isGuest) {
            $this->logHistory(); // beforeAction returning false skips afterAction
            Yii::$app->response->redirect(['site/login']);
            return false;
        }
        if ($this->requireAdmin) {
            $identity = Yii::$app->user->identity;
            if (!$identity instanceof \anginger\models\User || !$identity->isAdmin()) {
                $this->logHistory();
                throw new \yii\web\ForbiddenHttpException('需要管理員權限');
            }
        }
        return parent::beforeAction($action);
    }
}
