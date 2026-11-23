<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace anginger\controllers;

use anginger\components\BaseController;
use Yii;
use yii\web\Response;

class SiteController extends BaseController
{
    public function actionIndex(): Response
    {
        return $this->asJson([
            'description' => 'Anginger HR',
            'copyright' => '(c) 2026 Anginger HR',
        ]);
    }

    public function actionError(): Response
    {
        $exception = Yii::$app->errorHandler->exception;
        return $this->asJson([
            'description' => 'Anginger Error',
            'reason' => $exception ? $exception->getMessage() : 'unknown error',
        ]);
    }
}
