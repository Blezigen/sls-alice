<?php

namespace api\modules\collection\models\write;

class CollectionWrite extends \common\models\Collection
{
    public static function __docAttributeIgnore()
    {
        return [
            'id',
            'created_at',
            'updated_at',
        ];
    }
}
