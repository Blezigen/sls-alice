<?php

namespace tests\fixtures;

use common\models\CabinExcludeGroup;
use tests\modules\HistoryActiveFixture;

class CabinExcludeGroupFixture extends HistoryActiveFixture
{
    public $modelClass = CabinExcludeGroup::class;
    public $dataFile = __DIR__ . '/../_data/cabin_exclude_groups.php';

    public $depends = [CabinExcludeFixture::class];
}
