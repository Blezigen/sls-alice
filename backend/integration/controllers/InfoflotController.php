<?php

namespace integration\controllers;

use yii\web\Controller;
use integration\services\InfoflotService;

/*
    Пример интерации Инфофлота
*/

class InfoflotController extends Controller
{
    public function actionIndex()
    {
        return 'Инфофлот';
    }

    public function actionCreate($id)
    {
        // Добавление заявки
        return $this->service()->createOrder($id);
    }

    public function actionImport()
    {
        // Импорт туров и цен
        return $this->service()->importData();
    }

    public function actionImportShips()
    {
        // Импорт кораблей и кабин
        return $this->service()->importShips();
    }

    public function actionImportCities()
    {
        // Импорт городов
        return $this->service()->importCities();
    }

    protected function service()
    {
        return new InfoflotService();
    }
}
