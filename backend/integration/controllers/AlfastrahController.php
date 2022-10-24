<?php

namespace integration\controllers;

use yii\web\Controller;
use integration\services\AlfastrahService;

/*
    Пример интерации Альфастрахования
*/

class AlfastrahController extends Controller
{
    public function actionIndex()
    {
        // Получение программ
        $data = $this->service()->GetInsuranceProgramms();

        // Получение рисков
        $risks = $this->service()->GetRisks($data[2]->insuranceProgrammUID);

        return [
            'data' => $data,
            'risks' => $risks
        ];
    }

    public function actionCalculate($id)
    {
        // Расчет нового полиса
        return $this->service()->calculatePolicty($id);
    }

    public function actionCreate($id)
    {
        // Создание нового полиса
        return $this->service()->createPolicty($id);
    }

    private function service()
    {
        return new AlfastrahService();
    }
}
