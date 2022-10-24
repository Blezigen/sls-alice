<?php

namespace common;

use yii\base\Model;

class SkipTake extends Model
{
    public int|string $take;
    public int|string $skip;
}