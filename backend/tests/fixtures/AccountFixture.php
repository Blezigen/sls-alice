<?php

namespace tests\fixtures;

use common\models\Account;
use tests\modules\ActiveFixture;

class AccountFixture extends ActiveFixture
{
    public $modelClass = Account::class;
    public $dataFile = __DIR__."/../_data/accounts.php";
    public $depends = [CollectionFixture::class];
}
