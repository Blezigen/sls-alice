<?php

namespace tests\fixtures;

use common\models\VirtualCabin;
use tests\modules\HistoryActiveFixture;

class VirtualCabinsFixture extends HistoryActiveFixture
{
    public $modelClass = VirtualCabin::class;
    public $dataFile = __DIR__ . '/../_data/virtual_cabins.php';

    public $depends = [
        CabinFixture::class,
    ];

    public function getDeleteRuleName()
    {
        return 'chk_delete_virtual_cabins';
    }
}
