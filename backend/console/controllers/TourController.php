<?php

namespace console\controllers;

use Carbon\Carbon;
use common\models\Tour;
use console\AbstractConsoleController;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableCellStyle;
use Symfony\Component\Console\Helper\TableSeparator;
use yii\helpers\ArrayHelper;

class TourController extends AbstractConsoleController
{
    public function actionIndex()
    {
        $table = new Table($this->output);

        $tourQ = Tour::find()
            ->andWhere(['IS NOT', 'ship_id', null])
            ->andWhere(['IS NOT', 'departure_dt', null])
            ->andWhere(['IS NOT', 'arrival_dt', null])
//            ->andWhere(['=', 'is_visible', true])
//            ->andWhere(['=', 'is_canceled', false])
            ->joinWith([
                'ship',
            ])
            ->andWhere(["tours.id" => 53])
            ->limit(10)
            ->orderBy('id');

//        dd($tourQ->createCommand()->rawSql);

        $tours = $tourQ->all();

        $table->setHeaderTitle('Tours');
        $table->setFooterTitle('Страница 1/2');

        $table->setHeaders([
            'Изм.',
            'Вид',
            'Кол-во людей',
            'План / Бронь',
            'Оплаты',
            'Комиссия агента',
            'Скидки',
            'Теплоход',
            'Дата тура',
            'Название тура',
        ]);

        $tours = ArrayHelper::map($tours, 'id', function (Tour $data) {
            $options = [];

            if ($data->isCanceled()) {
                $options = [
                    'style' => new TableCellStyle([
                        'fg' => 'red',
//                        'bg'         => 'green',
                        // or
//                        'cellFormat' => '<info>%s</info>',
                    ]),
                ];
            }

            return [
                new TableCell($data->id, $options),
                new TableCell($data->isVisible() ? '<◊>' : '', $options),
                new TableCell("$data->orderTouristCount / $data->orderCabinCount", $options),
                new TableCell("$data->planAmount / $data->reserveAmount", $options),
                new TableCell("$data->paymentAmount", $options),
                new TableCell("$data->comissionAmount", $options),
                new TableCell("$data->discountAmount", $options),
                new TableCell($data->ship?->title, $options),
                new TableCell("$data->departure_dt\n$data->arrival_dt", $options),
                new TableCell("$data->title", $options),
            ];
        });

        foreach ($tours as $tour) {
            $table->addRow($tour);
            $table->addRow(new TableSeparator());
        }

        $table->render();

        return 1;
    }
}
