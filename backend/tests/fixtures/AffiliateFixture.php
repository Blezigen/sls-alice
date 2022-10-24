<?php

namespace tests\fixtures;

use common\models\Account;
use common\models\Affiliate;
use tests\modules\ActiveFixture;

class AffiliateFixture extends ActiveFixture
{
    public $modelClass = Affiliate::class;
    public $dataFile = __DIR__."/../_data/affiliates.php";
}
