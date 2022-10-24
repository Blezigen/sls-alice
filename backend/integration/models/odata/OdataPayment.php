<?php

namespace integration\models\odata;

/*
Очень часто бывают возвраты покупателю.
Еще есть переносы оплаты с одной заявки на другую
Нужно предусмотреть отмену/изменение документа оплаты.
Необходима функция групповой выгрузки документов по фильтру в 1с.
Можем добавить очередь для выгрузки документов.
Со стороны 1с мы заполняем очередь со ссылками на документы, вы забираете и освобождаете очередь. Думаю достаточно будет сделать регистр сведений для очереди.
Еще один регистр сведений можно создать для второй очереди - в неё вы будете вносить данные о изменениях в системе бронирования. Лучше записывать туда ВСЕ значимые изменения в базе, а мы со стороны 1с будем сами чистить очередь и забирать нужные сведения. 
Лучше с вашей стороны тоже создать 2 очереди.
Локально с каждой стороны наполняем очередь, затем, по таймеру, делаем пакетную передачу данных в формате json, что-бы не устраивать кучу лишнего трафика.
Лучше использовать сжатие.
Вы создаете Новые документы, а изменения мы забираем из очереди. Тогда не будет конфликтов в данных
Давайте согласуем нужные поля и я внесу нужные изменения со стороны 1с
Готов обсудить подробнее

AccumulationRegister_ОплатаСчетов
Document_ОплатаПлатежнойКартой
Document_ПоступлениеНаРасчетныйСчет
Document_ПриходныйКассовыйОрдер
Document_РасходныйКассовыйОрдер
Document__СпутникГермес_ПереносПлатежейКруиз
*/

class OdataPayment extends OdataBase
{
    public static function tableName()
    {
        return 'Document_ПоступлениеНаРасчетныйСчет';
    }

    public static function import($limit = 10, $offset = 0)
    {
        $result = [];

        $operations = [
            "ОплатаПокупателя",
            "ОплатаПутевки",
            "ПоступленияОтПродажПоПлатежнымКартамИБанковскимКредитам"
        ];

        $model = static::find()
            ->orderBy("Date desc")
            ->where([
                // 'DeletionMark' => false,
                ['in', 'ВидОперации', $operations],
            ])
            ->limit($limit)
            ->offset($offset)
            ->all();

        return $model;

        foreach ($model as $data) {
            $payment = self::importData($data);

            $result[] = [
                'payment' => $payment,
                'data' => $data
            ];
        }

        return $result;
    }

    protected static function importData($data)
    {
        $model = new static;
        $service = $model->importService();

        $externalId = $data->{"_СпутникГермес_Путевка"};

        if ($externalId == "00000000-0000-0000-0000-000000000000") {
            return false;
        }

        $order = $service->getOrderByExternalID($externalId);

        if (!$order) {
            return false;
        }

        $payment = $service->importPayment([
            "order_id" => $order->id,
            "payment_amount" => $data->{"СуммаДокумента"},
            "description" => $data->{"Комментарий"},
            // "payment_number" => $data->{"Number"},
            "payment_number" => $data->{"Ref_Key"}
        ], $data->{"Ref_Key"});

        return $payment;
    }
}
