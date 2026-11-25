<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

namespace anginger\modules\account\controllers;

use anginger\controllers\PageBaseController;
use anginger\models\Employee;
use anginger\models\User;

class PageController extends PageBaseController
{
    public function actionIndex(): string
    {
        $items = User::find()->with('employee')->orderBy(['username' => SORT_ASC])->all();
        $employees = Employee::find()->orderBy(['emp_name' => SORT_ASC])->all();
        return $this->render('index', ['items' => $items, 'employees' => $employees]);
    }
}
