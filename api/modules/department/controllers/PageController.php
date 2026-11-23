<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace anginger\modules\department\controllers;

use anginger\controllers\PageBaseController;
use anginger\models\Department;
use anginger\models\Employee;

class PageController extends PageBaseController
{
    public function actionIndex(): string
    {
        $items = Department::find()->orderBy(['dep_name' => SORT_ASC])->all();
        $employees = Employee::find()->orderBy(['emp_name' => SORT_ASC])->all();
        return $this->render('index', ['items' => $items, 'employees' => $employees]);
    }
}
