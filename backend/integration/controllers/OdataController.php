<?php

namespace integration\controllers;

use yii\web\Controller;
use integration\services\OdataService;
use integration\services\OdataImportService;

use integration\models\odata\OdataOrder;
use integration\models\odata\OdataContragent;
use integration\models\odata\OdataShip;
use integration\models\odata\OdataTour;
use integration\models\odata\OdataPayment;
use integration\models\odata\OdataAvz;
use integration\models\odata\OdataOrganization;
use integration\models\odata\OdataPaymentTransfer;

// use integration\models\odata\OdataOrderRecord;

/*
    Пример интеграции 1C OData
*/

class OdataController extends Controller
{
    public function actionIndex()
    {
        $service = new OdataService();

        return [
            // 'result' => $service->get('InformationRegister_ШаблоныПечатиМашиночитаемыхФорм'),
            'result2' => $service->get('Document__СпутникГермес_СчетНаОплатуТураНаТеплоходе', [
                '$orderby' => 'Date desc',
                '$top' => 100
            ]),
        ];
    }

    protected function service()
    {
        return new OdataImportService();
    }

    public function actionContragents()
    {
        $model = OdataContragent::import($limit = 10, $offset = 0);

        return [
            "limit" => $limit,
            "offset" => $offset,
            "model" => $model
        ];
    }

    public function actionOrganization()
    {
        $model = OdataOrganization::import($limit = 10, $offset = 0);

        return [
            "limit" => $limit,
            "offset" => $offset,
            "model" => $model
        ];
    }

    public function actionShips()
    {
        $model = OdataShip::import($limit = 10, $offset = 0);

        return [
            "limit" => $limit,
            "offset" => $offset,
            "model" => $model
        ];
    }

    public function actionTours()
    {
        $model = OdataTour::import($limit = 10, $offset = 0);

        return [
            "limit" => $limit,
            "offset" => $offset,
            "model" => $model
        ];
    }

    public function actionOrders()
    {
        $model = OdataOrder::import($limit = 100, $offset = 0);

        return [
            "limit" => $limit,
            "offset" => $offset,
            "model" => $model
        ];
    }

    public function actionPayments()
    {
        $model = OdataPayment::import($limit = 100, $offset = 0);

        return [
            "limit" => $limit,
            "offset" => $offset,
            "model" => $model
        ];
    }

    public function actionPaymentsTransfer()
    {
        $model = OdataPaymentTransfer::import($limit = 10, $offset = 0);

        return [
            "limit" => $limit,
            "offset" => $offset,
            "model" => $model
        ];
    }

    public function actionAvz()
    {
        $model = OdataAvz::import($limit = 10, $offset = 0);

        return [
            "limit" => $limit,
            "offset" => $offset,
            "model" => $model
        ];
    }
}
