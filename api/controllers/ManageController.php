<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace anginger\controllers;

use anginger\components\AccessFilter;
use anginger\components\BaseController;
use anginger\models\Department;
use anginger\models\Employee;
use anginger\models\User;
use Ramsey\Uuid\Uuid;
use Yii;
use yii\web\Response;

class ManageController extends BaseController
{
    private const EMPLOYEE_FIELDS = [
        'emp_name', 'job_title', 'dep_id', 'city', 'address',
        'phone', 'zip_code', 'month_salary', 'annual_leave',
    ];

    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessFilter::class,
            ],
        ]);
    }

    /**
     * GET /manage/ - overview statistics for the dashboard.
     */
    public function actionIndex(): Response
    {
        $employees = Employee::find()->asArray()->all();
        $employeesCount = count($employees);
        $employeesSalariesAvg = $employeesCount > 0
            ? array_sum(array_column($employees, 'month_salary')) / $employeesCount
            : 0;
        $employeesCityCount = count(array_unique(array_column($employees, 'city')));

        $departments = Department::find()->asArray()->all();
        $departmentsCount = count($departments);
        $departmentsSalariesAvg = 0;
        $departmentsEmployeesCounts = [];
        foreach ($departments as $department) {
            $deptEmployees = Employee::find()->asArray()->where(['dep_id' => $department['dep_id']])->all();
            $departmentsSalariesAvg += $departmentsCount > 0
                ? array_sum(array_column($deptEmployees, 'month_salary')) / $departmentsCount
                : 0;
            $departmentsEmployeesCounts[] = [$department['dep_name'], count($deptEmployees)];
        }
        $departmentsEmployeesCountAvg = $departmentsCount > 0
            ? array_sum(array_map(fn (array $item) => $item[1], $departmentsEmployeesCounts)) / $departmentsCount
            : 0;

        return $this->asJson([
            'employees' => [
                'count' => $employeesCount,
                'city_count' => $employeesCityCount,
                'salaries_avg' => round($employeesSalariesAvg),
            ],
            'departments' => [
                'count' => $departmentsCount,
                'salaries_avg' => round($departmentsSalariesAvg),
                'employees_count_avg' => round($departmentsEmployeesCountAvg),
            ],
            'departments_employees_counts' => $departmentsEmployeesCounts,
        ]);
    }

    /**
     * GET /manage/employees - all employees, each with its department attached.
     */
    public function actionAllEmployees(): Response
    {
        $result = array_map(
            fn (Employee $employee) => $employee->toArrayWithDepartment(),
            Employee::find()->all()
        );
        return $this->asJson($result);
    }

    /**
     * GET /manage/employee?emp_id=...
     */
    public function actionGetEmployee(): Response
    {
        $empId = Yii::$app->getRequest()->getQueryParam('emp_id');
        if (empty($empId)) {
            return $this->respondError(400, 'bad request');
        }
        $employee = Employee::findOne(['emp_id' => $empId]);
        if ($employee === null) {
            return $this->respondError(404, 'not found');
        }
        return $this->asJson($employee->toArray());
    }

    /**
     * POST /manage/employee - create an employee, 201.
     */
    public function actionPostEmployee(): Response
    {
        $form = Yii::$app->getRequest()->post();
        foreach (self::EMPLOYEE_FIELDS as $field) {
            if (!isset($form[$field]) || $form[$field] === '') {
                return $this->respondError(400, 'bad request');
            }
        }
        if (Department::findOne(['dep_id' => $form['dep_id']]) === null) {
            return $this->respondError(400, 'bad request');
        }
        $employee = new Employee();
        // UUIDv7: time-ordered, so primary keys stay sequential and index-friendly.
        $employee->emp_id = Uuid::uuid7()->toString();
        $this->assignEmployeeFields($employee, $form);
        $employee->insert();
        return $this->respondEmpty(201);
    }

    /**
     * PUT /manage/employee - replace an employee, 204.
     */
    public function actionPutEmployee(): Response
    {
        $form = Yii::$app->getRequest()->post();
        if (!isset($form['emp_id'])) {
            return $this->respondError(400, 'bad request');
        }
        foreach (self::EMPLOYEE_FIELDS as $field) {
            if (!isset($form[$field]) || $form[$field] === '') {
                return $this->respondError(400, 'bad request');
            }
        }
        if (Department::findOne(['dep_id' => $form['dep_id']]) === null) {
            return $this->respondError(400, 'bad request');
        }
        $employee = Employee::findOne(['emp_id' => $form['emp_id']]);
        if ($employee === null) {
            return $this->respondError(404, 'not found');
        }
        $this->assignEmployeeFields($employee, $form);
        $employee->update();
        return $this->respondEmpty(204);
    }

    /**
     * DELETE /manage/employee?emp_id=... - 400 when the employee owns an account.
     */
    public function actionDeleteEmployee(): Response
    {
        $empId = Yii::$app->getRequest()->getQueryParam('emp_id');
        if (empty($empId)) {
            return $this->respondError(400, 'bad request');
        }
        if (User::findOne(['uuid' => $empId]) !== null) {
            return $this->respondError(400, 'bad request');
        }
        $employee = Employee::findOne(['emp_id' => $empId]);
        if ($employee === null) {
            return $this->respondError(404, 'not found');
        }
        $employee->delete();
        return $this->asJson(['message' => 'success']);
    }

    /**
     * GET /manage/departments - all departments, each with its manager attached.
     */
    public function actionAllDepartments(): Response
    {
        $result = array_map(
            fn (Department $department) => $department->toArrayWithManager(),
            Department::find()->all()
        );
        return $this->asJson($result);
    }

    /**
     * GET /manage/department?dep_id=...
     */
    public function actionGetDepartment(): Response
    {
        $depId = Yii::$app->getRequest()->getQueryParam('dep_id');
        if (empty($depId)) {
            return $this->respondError(400, 'bad request');
        }
        $department = Department::findOne(['dep_id' => $depId]);
        if ($department === null) {
            return $this->respondError(404, 'not found');
        }
        return $this->asJson($department->toArray());
    }

    /**
     * GET /manage/department/employees?dep_id=...
     */
    public function actionDepartmentEmployees(): Response
    {
        $depId = Yii::$app->getRequest()->getQueryParam('dep_id');
        $department = $depId ? Department::findOne(['dep_id' => $depId]) : null;
        if ($department === null) {
            return $this->respondError(404, 'not found');
        }
        return $this->asJson(
            Employee::find()->asArray()->where(['dep_id' => $depId])->all()
        );
    }

    /**
     * POST /manage/department - create a department, 201.
     */
    public function actionPostDepartment(): Response
    {
        $form = Yii::$app->getRequest()->post();
        if (empty($form['dep_name']) || empty($form['manager_emp_id'])) {
            return $this->respondError(400, 'bad request');
        }
        if (Employee::findOne(['emp_id' => $form['manager_emp_id']]) === null) {
            return $this->respondError(400, 'bad request');
        }
        $department = new Department();
        $department->dep_id = strtoupper(bin2hex(random_bytes(4)));
        $department->dep_name = $form['dep_name'];
        $department->manager_emp_id = $form['manager_emp_id'];
        $department->insert();
        Yii::$app->getResponse()->statusCode = 201;
        return $this->asJson($department->toArray());
    }

    /**
     * PUT /manage/department - replace a department, 204.
     */
    public function actionPutDepartment(): Response
    {
        $form = Yii::$app->getRequest()->post();
        if (empty($form['dep_id']) || empty($form['dep_name']) || empty($form['manager_emp_id'])) {
            return $this->respondError(400, 'bad request');
        }
        $department = Department::findOne(['dep_id' => $form['dep_id']]);
        if ($department === null) {
            return $this->respondError(404, 'not found');
        }
        if (Employee::findOne(['emp_id' => $form['manager_emp_id']]) === null) {
            return $this->respondError(400, 'bad request');
        }
        $department->dep_name = $form['dep_name'];
        $department->manager_emp_id = $form['manager_emp_id'];
        $department->update();
        return $this->respondEmpty(204);
    }

    /**
     * PATCH /manage/department?emp_id=...&dep_id=... - move an employee into a department.
     */
    public function actionPatchDepartment(): Response
    {
        $request = Yii::$app->getRequest();
        $empId = $request->getQueryParam('emp_id');
        $depId = $request->getQueryParam('dep_id');
        $employee = $empId ? Employee::findOne(['emp_id' => $empId]) : null;
        if ($employee === null) {
            return $this->respondError(404, 'not found');
        }
        $department = $depId ? Department::findOne(['dep_id' => $depId]) : null;
        if ($department === null) {
            return $this->respondError(404, 'not found');
        }
        $employee->dep_id = $department->dep_id;
        $employee->update();
        return $this->asJson(['message' => 'success']);
    }

    /**
     * DELETE /manage/department?dep_id=... - 400 while the department still has employees.
     */
    public function actionDeleteDepartment(): Response
    {
        $depId = Yii::$app->getRequest()->getQueryParam('dep_id');
        if (empty($depId)) {
            return $this->respondError(400, 'bad request');
        }
        $department = Department::findOne(['dep_id' => $depId]);
        if ($department === null) {
            return $this->respondError(404, 'not found');
        }
        if (Employee::find()->where(['dep_id' => $depId])->exists()) {
            return $this->respondError(400, 'bad request');
        }
        $department->delete();
        return $this->asJson(['message' => 'success']);
    }

    private function assignEmployeeFields(Employee $employee, array $form): void
    {
        foreach (self::EMPLOYEE_FIELDS as $field) {
            $employee->$field = $form[$field];
        }
    }
}
