<?php

namespace tests\fixtures;

use common\models\OrderPlace;
use tests\modules\HistoryActiveFixture;

class PermissionRuleFixture extends HistoryActiveFixture
{
    public $modelClass = OrderPlace::class;
    public $dataFile = __DIR__ . '/../_data/permission_rule.php';

    public $depends = [AccountFixture::class];
}
