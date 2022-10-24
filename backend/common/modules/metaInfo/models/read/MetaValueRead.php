<?php

namespace common\modules\metaInfo\models\read;

use common\BaseModel;

class MetaValueRead extends BaseModel
{
    public $id;
    public $key;
    public $title;
    public $type;
    public $rule;
    public $payload;
    public $value;
}
