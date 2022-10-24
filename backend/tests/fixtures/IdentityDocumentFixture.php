<?php

namespace tests\fixtures;

use common\models\IdentityDocument;
use tests\modules\HistoryActiveFixture;

class IdentityDocumentFixture extends HistoryActiveFixture
{
    public $modelClass = IdentityDocument::class;
    public $dataFile = __DIR__ . '/../_data/identity_document.php';

    public $depends = [];

    public function getDeleteRuleName()
    {
        return 'chk_delete_identity_documents';
    }
}
