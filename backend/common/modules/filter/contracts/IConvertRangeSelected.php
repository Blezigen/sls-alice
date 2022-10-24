<?php

namespace common\modules\filter\contracts;

interface IConvertRangeSelected
{
    public function getSelectedMin();

    public function getSelectedMax();
}
