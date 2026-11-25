<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace anginger\controllers;

use Ramsey\Uuid\Uuid;
use Yii;
use yii\db\ActiveRecord;
use yii\db\IntegrityException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * Generic CRUD resource controller (same pattern as anginger-erp).
 *
 * Maps onto Yii2 actions `index` and `all`:
 *   GET    /api/<module>        -> index  (one row, primary key in the query string)
 *   POST   /api/<module>        -> index  (create, body params)
 *   PUT    /api/<module>        -> index  (update, body params)
 *   DELETE /api/<module>        -> index  (primary key in the query string)
 *   GET    /api/<module>/all    -> all
 */
abstract class ResourceController extends BaseController
{
    /** @return class-string<ActiveRecord> */
    abstract protected function modelClass(): string;

    /** Name of the query/body parameter that carries the primary key (e.g. "emp_id" or "uuid"). */
    abstract protected function pkParam(): string;

    /** @return string[] fields required in the POST body */
    abstract protected function requiredCreateFields(): array;

    /** @return string[] fields required in the PUT body (includes the pk param) */
    abstract protected function requiredUpdateFields(): array;

    abstract protected function fillCreate(array $form): ActiveRecord;

    abstract protected function fillUpdate(ActiveRecord $model, array $form): void;

    /** Hook: return a Response to abort creation, or null to proceed. */
    protected function validateCreate(array $form): ?Response
    {
        return null;
    }

    /** Hook: return a Response to abort the update, or null to proceed. */
    protected function validateUpdate(ActiveRecord $model, array $form): ?Response
    {
        return null;
    }

    /** Hook: return a Response to abort deletion, or null to proceed. */
    protected function beforeDelete(ActiveRecord $model): ?Response
    {
        return null;
    }

    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index' => ['GET', 'POST', 'PUT', 'DELETE'],
                    'all' => ['GET'],
                ],
            ],
        ]);
    }

    public function actionIndex(): Response
    {
        if (!$this->requireLogin()) {
            return $this->forbidden();
        }
        $request = Yii::$app->request;
        $modelClass = $this->modelClass();
        switch ($request->method) {
            case 'GET':
                $key = $request->getQueryParam($this->pkParam());
                if (!is_scalar($key) || $key === '') {
                    return $this->badRequest();
                }
                $model = $modelClass::findOne([$this->pkParam() => (string)$key]);
                return $model !== null ? $this->asJson($model) : $this->notFound();

            case 'POST':
                $form = $request->getBodyParams();
                foreach ($this->requiredCreateFields() as $field) {
                    if (!isset($form[$field])) {
                        return $this->badRequest();
                    }
                }
                if (($error = $this->validateCreate($form)) !== null) {
                    return $error;
                }
                $model = $this->fillCreate($form);
                try {
                    if (!$model->save()) {
                        Yii::$app->response->setStatusCode(400);
                        return $this->asJson(["message" => "validation failed", "errors" => $model->errors]);
                    }
                    return $this->emptyResponse(201);
                } catch (IntegrityException) {
                    return $this->conflict();
                }

            case 'PUT':
                $form = $request->getBodyParams();
                foreach ($this->requiredUpdateFields() as $field) {
                    if (!isset($form[$field])) {
                        return $this->badRequest();
                    }
                }
                if (!isset($form[$this->pkParam()]) || !is_scalar($form[$this->pkParam()])) {
                    return $this->badRequest();
                }
                $model = $modelClass::findOne([$this->pkParam() => (string)$form[$this->pkParam()]]);
                if ($model === null) {
                    return $this->notFound();
                }
                if (($error = $this->validateUpdate($model, $form)) !== null) {
                    return $error;
                }
                $this->fillUpdate($model, $form);
                try {
                    if (!$model->save()) {
                        Yii::$app->response->setStatusCode(400);
                        return $this->asJson(["message" => "validation failed", "errors" => $model->errors]);
                    }
                    return $this->emptyResponse(204);
                } catch (IntegrityException) {
                    return $this->conflict();
                }

            case 'DELETE':
                $key = $request->getQueryParam($this->pkParam());
                if (!is_scalar($key) || $key === '') {
                    return $this->badRequest();
                }
                $model = $modelClass::findOne([$this->pkParam() => (string)$key]);
                if ($model === null) {
                    return $this->notFound();
                }
                if (($error = $this->beforeDelete($model)) !== null) {
                    return $error;
                }
                $model->delete();
                return $this->emptyResponse(204);
        }
        return $this->badRequest();
    }

    public function actionAll(): Response
    {
        if (!$this->requireLogin()) {
            return $this->forbidden();
        }
        $modelClass = $this->modelClass();
        return $this->asJson($modelClass::find()->all());
    }

    /**
     * UUID v7 (RFC 9562) via ramsey/uuid: time-ordered, so uuid primary keys
     * insert sequentially into the B-tree instead of randomly (v4).
     */
    protected static function uuidV7(): string
    {
        return Uuid::uuid7()->toString();
    }
}
