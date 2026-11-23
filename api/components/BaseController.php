<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace anginger\components;

use Yii;
use yii\web\Controller;
use yii\web\Response;

class BaseController extends Controller
{
    public function behaviors(): array
    {
        return [
            'cors' => [
                'class' => CorsFilter::class,
            ],
        ];
    }

    /**
     * Send an empty response with the given HTTP status code.
     */
    protected function respondEmpty(int $status): Response
    {
        $response = Yii::$app->getResponse();
        $response->statusCode = $status;
        $response->format = Response::FORMAT_RAW;
        $response->content = '';
        return $response;
    }

    /**
     * Send a JSON error body with the given HTTP status code.
     */
    protected function respondError(int $status, string $message): Response
    {
        Yii::$app->getResponse()->statusCode = $status;
        return $this->asJson(['message' => $message]);
    }
}
