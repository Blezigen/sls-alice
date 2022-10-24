<?php

namespace integration\models\odata;

class OdataOrderRecord extends OdataActiveRecord
{
    public static function tableName()
    {
        return 'Document__СпутникГермес_СчетНаОплатуТураНаТеплоходе';
    }

    public function rules()
    {
        return [
            [[
                'Контрагент_Key',
                'СуммаДокумента',
                'Теплоход_Key',
                'Тур_Key',
                'ДатаБронирования',
            ], 'safe'],
        ];
    }
}
