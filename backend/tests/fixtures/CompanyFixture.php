<?php

namespace tests\fixtures;

use common\models\Company;
use tests\modules\ActiveFixture;

class CompanyFixture extends ActiveFixture
{
    public $modelClass = Company::class;
    public $dataFile = __DIR__ . '/../_data/companies.php';

    public $depends = [];

    public function getDeleteRuleName(): string
    {
        return 'chk_delete_companies';
    }
}