<?php

namespace integration\models\odata;

class OdataTour extends OdataBase
{
    public static function tableName()
    {
        return 'Catalog__СпутникГермес_ТурыНаТеплоходах';
    }

    public static function import($limit = 10, $offset = 0)
    {
        $result = [];

        $model = static::find()
            ->with("Owner")
            ->orderBy("ДатаНачала desc")
            ->where([
                'DeletionMark' => false,
            ])
            ->limit($limit)
            ->offset($offset)
            ->all();

        foreach ($model as $data) {
            $tour = static::importData($data);

            $result[] = [
                'tour' => $tour,
                'data' => $data
            ];
        }

        return $result;
    }

    protected static function importData($data)
    {
        $model = new static;
        $service = $model->importService();

        $shipAttr = $service->prepareShip($data->{"Owner"});
        $ship = $service->importShip($shipAttr, $data->{"Owner"}->{"Ref_Key"});

        if (!$ship) {
            return false;
        }

        $tourAttr = $service->prepareTour($data);
        $tour = $service->importTour($tourAttr, $data->{"Ref_Key"});

        if (!$tour) {
            return false;
        }

        $navigations = $service->prepareShipNavigations($data, $ship->id, $tour->id);

        if ($navigations) {
            foreach ($navigations as $navigation) {
                $service->importShipNavigation($navigation);
            }
        }

        return $tour;
    }
}
