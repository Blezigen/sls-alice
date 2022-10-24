<?php

namespace tests\fixtures;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use common\models\City;
use common\models\Ship;
use common\models\ShipNavigation;
use tests\modules\HistoryActiveFixture;

class ShipNavigationFixture extends HistoryActiveFixture
{
    public $modelClass = ShipNavigation::class;
    public $dataFile = __DIR__ . '/../_data/ship_navigation.php';

    public $depends = [CityFixture::class, ShipFixture::class];

    public function getDeleteRuleName()
    {
        return 'chk_delete_ship_navigations';
    }
}
