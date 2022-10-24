<?php

namespace integration\controllers;

use yii\web\Controller;
use integration\services\VodohodService;

/*
    Пример интерации Водоход
*/

class VodohodController extends Controller
{
    public function actionIndex()
    {
        return 'Водоход';
    }

    public function actionCreate($id)
    {
        // Добавление заявки
        return $this->service()->createOrder($id);
    }

    public function actionImport()
    {
        // Импорт туров, кабин и цен
        return $this->service()->importData();
    }

    public function actionImportShips()
    {
        // Импорт кораблей
        return $this->service()->importShips();
    }

    public function actionImportCities()
    {
        // Импорт городов
        return $this->service()->importCities();
    }

    protected function service()
    {
        return new VodohodService();
    }
}
