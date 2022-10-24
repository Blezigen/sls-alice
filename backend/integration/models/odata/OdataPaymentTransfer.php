<?php

namespace integration\models\odata;

use common\models\OrderPayment;

class OdataPaymentTransfer extends OdataBase
{
    public static function tableName()
    {
        return 'Document__СпутникГермес_ПереносПлатежейКруиз';
    }

    public static function import($limit = 10, $offset = 0)
    {
        $result = [];

        $model = static::find()
            // ->with("ПрежнийСчетНаОплатуТураНаТеплоходе")
            ->orderBy("Date desc")
            // ->where([
            //     // 'DeletionMark' => false,
            //     ['in', 'ВидОперации', $operations],
            // ])
            ->limit($limit)
            ->offset($offset)
            ->all();

        foreach ($model as $data) {
            $paymentTransfer = self::importData($data);

            $result[] = [
                'paymentTransfer' => $paymentTransfer,
                'data' => $data
            ];
        }

        return $result;
    }

    protected static function importData($data)
    {
        $model = new static;
        $service = $model->importService();

        $order = $service->getOrderByExternalID($data->{"ПрежнийСчетНаОплатуТураНаТеплоходе_Key"});

        // if (!$order) {
        //     return false;
        // }

        $payments = OdataPayment::find()
            ->orderBy("Date desc")
            ->where(['Document__СпутникГермес_СчетНаОплатуТураНаТеплоходе._СпутникГермес_Путевка' => $data->{"ПрежнийСчетНаОплатуТураНаТеплоходе_Key"}])
            ->limit(10)
            ->all();

        $arr = [];
        $arr2 = [];

        foreach ($payments as $p) {
            $arr[] = [
                "Ref_Key" => $p->{"Ref_Key"},
                "Number" => $p->{"Number"},
                "СуммаДокумента" => $p->{"СуммаДокумента"},
                "НазначениеПлатежа" => $p->{"НазначениеПлатежа"},
                "Комментарий" => $p->{"Комментарий"},
            ];
        }


        foreach ($data->{"Счета"} as $item) {
            $payments = OdataPayment::find()
                ->orderBy("Date desc")
                ->where(['Document__СпутникГермес_СчетНаОплатуТураНаТеплоходе._СпутникГермес_Путевка' => $item->{"СчетТ_Key"}])
                ->limit(10)
                ->all();

            foreach ($payments as $p) {
                $arr2[] = [
                    "Ref_Key" => $p->{"Ref_Key"},
                    "Number" => $p->{"Number"},
                    "СуммаДокумента" => $p->{"СуммаДокумента"},
                    "НазначениеПлатежа" => $p->{"НазначениеПлатежа"},
                    "Комментарий" => $p->{"Комментарий"},
                ];
            }
        }

        return [
            'p' => $arr,
            'pNew' => $arr2,
        ];

        if (!$payments) {
            return false;
        }

        return $payments;

        // $status = Collection::find()
        //         ->where(['slug' => IConstant::ORDER_PAYMENT_STATUS_NOT_PAYED])
        //         ->one();

        if ($order) {
            $orderPayments = OrderPayment::find()
                ->where([
                    // "order_id" => $order->id,
                    "payment_type" => $service->company
                ])
                ->all();

            return [
                'order' => $order,
                'orderPayments' => $orderPayments,
            ];
        }

        // return $payment;


        // $model = OdataOrder::find()
        //     ->where(["Ref_Key" => $itemPrev->{"Ref_Key"}])
        //     ->one();

        // var_dump($model);
        // die();

        return;
        /*
            1) Находим заявку (счет) и отменяем платеж по сумме
            2) Создаем (или ищем) новые счета и распределяем их по заявкам
        */

        foreach ($data->{"Счета"} as $item) {
            // $item->{"СчетТ_Key"}
            // $item->{"Сумма"}

            $model = static::find()
                ->where(["Ref_Key" => $item->{"СчетТ_Key"}])
                ->one();
        }

        die();

        return null;

        // $externalId = $data->{"_СпутникГермес_Путевка"};

        // if ($externalId == "00000000-0000-0000-0000-000000000000") {
        //     return false;
        // }

        // $order = $service->getOrderByExternalID($externalId);

        // if (!$order) {
        //     return false;
        // }

        // $payment = $service->importPayment([
        //     "order_id" => $order->id,
        //     "payment_amount" => $data->{"СуммаДокумента"},
        //     "description" => $data->{"Комментарий"},
        //     // "payment_number" => $data->{"Number"},
        //     "payment_number" => $data->{"Ref_Key"}
        // ], $data->{"Ref_Key"});

        // return $payment;
    }
}
