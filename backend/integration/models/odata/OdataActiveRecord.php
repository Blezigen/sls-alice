<?php

namespace integration\models\odata;

use execut\oData\ActiveRecord;

/*
    https://github.com/execut/yii2-1c-odata
*/

class OdataActiveRecord extends ActiveRecord
{
    public function getName()
    {
        return $this->__toString();
    }

    public function __toString()
    {
        return '#' . $this->Ref_Key;
    }

    public function getPrimaryKey($asArray = false)
    {
        return 'Ref_Key';
    }
}
