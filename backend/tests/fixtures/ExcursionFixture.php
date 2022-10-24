<?php

namespace tests\fixtures;

use common\models\Account;
use common\models\Affiliate;
use common\models\Excursion;
use tests\modules\ActiveFixture;

class ExcursionFixture extends ActiveFixture
{
    public $modelClass = Excursion::class;
    public $dataFile = __DIR__."/../_data/excursions.php";

    public $depends = [CityFixture::class];
}
