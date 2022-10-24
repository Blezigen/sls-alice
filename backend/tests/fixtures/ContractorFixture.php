<?php

namespace tests\fixtures;

use common\models\Contractor;
use common\models\DiscountCard;
use tests\modules\HistoryActiveFixture;

class ContractorFixture extends HistoryActiveFixture
{
    public $modelClass = Contractor::class;
    public $dataFile = __DIR__ . '/../_data/contractor.php';

    public $depends = [CollectionFixture::class, AccountFixture::class];

    public function getDeleteRuleName()
    {
        return 'chk_delete_contractors';
    }
}
