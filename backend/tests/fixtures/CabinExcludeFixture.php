<?php

namespace tests\fixtures;

use common\models\CabinExclude;
use tests\modules\HistoryActiveFixture;

class CabinExcludeFixture extends HistoryActiveFixture
{
    public $modelClass = CabinExclude::class;
    public $dataFile = __DIR__ . '/../_data/cabin_exclude.php';

    public $depends = [CabinFixture::class];

}
