<?php

namespace tests\fixtures;

use common\models\Discount;
use tests\modules\HistoryActiveFixture;

class DiscountFixture extends HistoryActiveFixture
{
    public $modelClass = Discount::class;
    public $dataFile = __DIR__ . '/../_data/discount.php';

    public $depends = [CollectionFixture::class];

    public function getDeleteRuleName()
    {
        return 'chk_delete_discounts';
    }
}
