<?php

namespace common\modules\acquiring\services;

use Carbon\Carbon;
use common\modules\acquiring\AcquiringData;
use common\modules\acquiring\contracts\IAcquiringProvider;
use common\modules\acquiring\PaymentAcquiringData;
use yii\base\Model;

class QrCodeAcquiringProvider extends Model implements IAcquiringProvider
{
    const PROVIDER = "qr-code";

    public function generatePaymentLink(AcquiringData $data): PaymentAcquiringData {
        $payment = new PaymentAcquiringData();
        $payment->acquiringOrderId = $data->orderNumber;

        try {
            $g = new \common\modules\acquiring\lib\Gost(
                "ООО \"Речные круизы\"",
                "40702810529220001546",
                "ФИЛИАЛ \"НИЖЕГОРОДСКИЙ\" АО \"АЛЬФА-БАНК\"",
                "042202824",
                "30101810200000000824"
            );
        } catch (\Throwable $ex) {
            dd($ex);
        }
        $g->setThrowExceptions(true);
        $g->setValidateOnSet(true);

        $dateNow = Carbon::now()->format('dmYHis');
        $date = Carbon::now()->format("d.m.Y");
        $host = env("API_URL");


        $g->KPP = "631801001";
        $g->PayeeINN = "6318040060";
        $g->LastName = "Матвеева Елена Александровна";
        $g->Purpose
            = "Оплата по счету № {$data->orderNumber} от $date Сумма {$data->amount} руб. НДС не облагается.";
        $g->Sum = $data->amount;
//        $g-> = "b4a45a296f4b74aa05f9ad277bcac877";
        $g->Nmdt = "$host/acquiring/qr-code/{$payment->acquiringOrderId}_{$dateNow}.png";

        $g->render(\Yii::getAlias("@root/api/web/temp/{$payment->acquiringOrderId}_{$dateNow}.png"));

        $payment->paymentUrl = $g->Nmdt;

        return $payment;
    }

    public function info($acquiringOrderId, $orderId)
    {
        return [];
    }

    public function deposit($acquiringOrderId, $amount)
    {
        return [];
    }

    public function processCallbackRequest(\yii\base\Request $request){}
}