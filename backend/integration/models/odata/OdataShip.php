<?php

namespace integration\models\odata;

class OdataShip extends OdataBase
{
    public static function tableName()
    {
        return 'Catalog__СпутникГермес_Теплоходы';
    }

    public static function import($limit = 10, $offset = 0)
    {
        $result = [];

        $model = static::find()
            ->orderBy("Code desc")
            ->where([
                'DeletionMark' => false,
                ['!=', 'Description', '']
            ])
            ->limit($limit)
            ->offset($offset)
            ->all();

        foreach ($model as $data) {
            $ship = static::importData($data);

            $result[] = [
                'ship' => $ship,
                'data' => $data
            ];
        }

        return $result;
    }

    protected static function importData($data)
    {
        $model = new static;
        $service = $model->importService();

        $shipAttr = $service->prepareShip($data);
        $ship = $service->importShip($shipAttr, $data->{"Ref_Key"});

        return $ship;
    }
}
