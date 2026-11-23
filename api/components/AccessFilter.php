<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace anginger\components;

use Yii;
use yii\base\ActionFilter;
use yii\web\Response;

/**
 * Requires a valid login session. Actions listed in $except (e.g. the
 * login action itself) stay public. The logged-in user is exposed through
 * CurrentUser::get().
 */
class AccessFilter extends ActionFilter
{
    /**
     * @var string[] action IDs that do not require authentication
     */
    public array $exceptActions = [];

    public function beforeAction($action): bool
    {
        if (in_array($action->id, $this->exceptActions, true)) {
            return true;
        }
        if (CurrentUser::get() === null) {
            $response = Yii::$app->getResponse();
            $response->statusCode = 401;
            $response->format = Response::FORMAT_JSON;
            $response->data = ['message' => 'unauthorized'];
            return false;
        }
        return true;
    }
}
