<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace anginger\controllers;

use anginger\models\Department;
use anginger\models\Employee;
use anginger\models\History;
use Yii;
use yii\web\Response;

/**
 * Server-rendered pages: overview dashboard, login, logout, error.
 * The JSON API lives in AuthenticController + the resource modules.
 */
class SiteController extends BaseController
{
    public $layout = '@app/views/layouts/main';

    public function actionIndex(): string|Response
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }
        $employees = Employee::find()->asArray()->all();
        $employeesCount = count($employees);
        $departments = Department::find()->asArray()->all();
        $departmentsCount = count($departments);
        $departmentsEmployeesCounts = [];
        $departmentsSalariesAvg = 0;
        foreach ($departments as $department) {
            $deptEmployees = Employee::find()->asArray()->where(['dep_id' => $department['dep_id']])->all();
            $departmentsEmployeesCounts[] = [$department['dep_name'], count($deptEmployees)];
            $departmentsSalariesAvg += $departmentsCount > 0
                ? array_sum(array_column($deptEmployees, 'month_salary')) / $departmentsCount
                : 0;
        }
        $stats = [
            'employees_count' => $employeesCount,
            'employees_city_count' => count(array_unique(array_column($employees, 'city'))),
            'employees_salaries_avg' => $employeesCount > 0
                ? round(array_sum(array_column($employees, 'month_salary')) / $employeesCount)
                : 0,
            'departments_count' => $departmentsCount,
            'departments_salaries_avg' => round($departmentsSalariesAvg),
            'departments_employees_count_avg' => $departmentsCount > 0
                ? round(array_sum(array_map(fn (array $item) => $item[1], $departmentsEmployeesCounts)) / $departmentsCount)
                : 0,
            'departments_employees_counts' => $departmentsEmployeesCounts,
        ];
        $histories = History::find()->orderBy(['created_time' => SORT_DESC])->limit(100)->all();
        return $this->render('index', ['stats' => $stats, 'histories' => $histories]);
    }

    public function actionLogin(): string|Response
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['site/index']);
        }
        $this->layout = '@app/views/layouts/blank';
        return $this->render('login');
    }

    public function actionLogout(): Response
    {
        Yii::$app->user->logout();
        return $this->redirect(['site/login']);
    }

    public function actionError(): string
    {
        $this->layout = '@app/views/layouts/blank';
        $exception = Yii::$app->errorHandler->exception;
        if ($exception !== null && method_exists($exception, 'getStatusCode')) {
            Yii::$app->response->setStatusCode($exception->getStatusCode());
        }
        return $this->render('error', ['exception' => $exception ?? new \Exception('unknown error')]);
    }
}
