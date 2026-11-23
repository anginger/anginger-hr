<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace anginger\components;

use Yii;
use yii\base\ActionFilter;

/**
 * Answers CORS preflight requests and decorates every response with
 * permissive CORS headers so the SPA (which sends credentials) can call
 * the API cross-origin during development.
 */
class CorsFilter extends ActionFilter
{
    public function beforeAction($action): bool
    {
        $request = Yii::$app->getRequest();
        $response = Yii::$app->getResponse();

        $origin = $request->getHeaders()->get('Origin');
        $response->getHeaders()
            ->set('Access-Control-Allow-Origin', $origin ?: '*')
            ->set('Access-Control-Allow-Credentials', 'true')
            ->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            ->set('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Requested-With')
            ->set('Access-Control-Max-Age', '86400');

        if ($request->getMethod() === 'OPTIONS') {
            $response->statusCode = 204;
            $response->format = \yii\web\Response::FORMAT_RAW;
            $response->content = '';
            return false;
        }

        return true;
    }
}
