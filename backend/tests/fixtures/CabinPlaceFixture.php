<?php

namespace tests\fixtures;

use common\models\CabinPlace;
use tests\modules\HistoryActiveFixture;

class CabinPlaceFixture extends HistoryActiveFixture
{
    public $modelClass = CabinPlace::class;
    public $dataFile = __DIR__ . '/../_data/cabin_places.php';

    public $depends = [
        CollectionFixture::class,
        CabinFixture::class,
    ];

    public function getDeleteRuleName()
    {
        return 'chk_delete_cabin_places';
    }
}
