<?php

namespace integration\controllers;

use yii\web\Controller;
use integration\services\RussiatourismService;

/*
    Пример интерации Ростуризма
*/

class RussiatourismController extends Controller
{
    public function actionIndex()
    {
        return "Ростуризм";
    }

    public function actionCreate($id)
    {
        return $this->service()->CreateVoucherByOrderID($id);
    }

    public function actionCity()
    {
        $this->service()->importCities();
    }

    protected function service()
    {
        return new RussiatourismService();
    }
}
