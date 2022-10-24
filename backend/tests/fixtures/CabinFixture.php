<?php

namespace tests\fixtures;

use common\models\Cabin;
use tests\modules\HistoryActiveFixture;

class CabinFixture extends HistoryActiveFixture
{
    public $modelClass = Cabin::class;
    public $dataFile = __DIR__ . '/../_data/cabins.php';

    public $depends = [
        CabinExcludeGroupFixture::class,
        CabinExcludeFixture::class,
        ShipFixture::class,
        ShipCabinClassFixture::class,
    ];

    public function getDeleteRuleName()
    {
        return 'chk_delete_cabins';
    }
}
