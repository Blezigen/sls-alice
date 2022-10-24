<?php

namespace tests\fixtures;

use common\models\Region;
use tests\modules\HistoryActiveFixture;

class RegionFixture extends HistoryActiveFixture
{
    public $modelClass = Region::class;
    public $dataFile = __DIR__ . '/../_data/region.php';

    public $depends = [];

    public function getDeleteRuleName()
    {
        return 'chk_delete_regions';
    }
}
