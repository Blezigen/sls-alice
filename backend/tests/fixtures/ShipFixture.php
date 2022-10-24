<?php

namespace tests\fixtures;

use common\models\Ship;
use tests\modules\HistoryActiveFixture;

class ShipFixture extends HistoryActiveFixture
{
    public $modelClass = Ship::class;
    public $dataFile = __DIR__ . '/../_data/ships.php';

    public $depends = [CollectionFixture::class];

    public function getDeleteRuleName()
    {
        return 'chk_delete_ships';
    }
}
