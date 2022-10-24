<?php

namespace integration\controllers;

use yii\web\Controller;
use integration\services\MintransService;

/*
    Пример интерации Минтранс
*/

class MintransController extends Controller
{
    public function actionIndex()
    {
        return $this->service()->sendTours();
    }

    public function actionStation()
    {
        return $this->service()->sendStation();
    }

    public function actionTimetable()
    {
        return $this->service()->sendTimetable();
    }

    public function actionResult($id)
    {
        return $this->service()->getResult($id);
    }

    protected function service()
    {
        return new MintransService();
    }
}
