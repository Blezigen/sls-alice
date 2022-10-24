<?php

namespace common\behaviors;

use Carbon\Carbon;

class SoftDeleteBehavior extends \yiithings\softdelete\behaviors\SoftDeleteBehavior
{
    protected function getValue($event)
    {
        if ($this->value === null) {
            return Carbon::now()->format(Carbon::DEFAULT_TO_STRING_FORMAT);
        }

        return parent::getValue($event);
    }
}
