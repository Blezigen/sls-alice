<?php

namespace integration\models\odata;

class OdataAvz extends OdataBase
{
    public static function tableName()
    {
        return 'Document_АВЗ_Страховка';
    }

    public static function import($limit = 10, $offset = 0)
    {
        $result = [];

        $model = static::find()
            ->orderBy("Date desc")
            // ->where([
            //     'DeletionMark' => false,
            //     ['!=', 'НаименованиеПолное', '']
            // ])
            ->limit($limit)
            ->offset($offset)
            ->all();

        foreach ($model as $data) {
            $avz = static::importData($data);

            $result[] = [
                'avz' => $avz,
                'data' => $data
            ];
        }

        return $result;
    }

    protected static function importData($data)
    {
        $model = new static;
        $service = $model->importService();

        return null;
    }
}
