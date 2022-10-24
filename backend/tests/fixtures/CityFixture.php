<?php

namespace tests\fixtures;

use common\models\City;
use tests\modules\HistoryActiveFixture;

class CityFixture extends HistoryActiveFixture
{
    public $modelClass = City::class;
    public $dataFile = __DIR__ . '/../_data/city.php';

    public $depends = [
        RegionFixture::class,
    ];

    public function getDeleteRuleName()
    {
        return 'chk_delete_cities';
    }
}
