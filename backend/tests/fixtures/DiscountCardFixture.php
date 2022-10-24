<?php

namespace tests\fixtures;

use common\models\DiscountCard;
use tests\modules\HistoryActiveFixture;

class DiscountCardFixture extends HistoryActiveFixture
{
    public $modelClass = DiscountCard::class;
    public $dataFile = __DIR__ . '/../_data/discount_card.php';

    public $depends = [CollectionFixture::class];

    public function getDeleteRuleName()
    {
        return 'chk_delete_discount_cards';
    }
}
