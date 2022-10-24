<?php

namespace tests;

use yii\helpers\ArrayHelper;

class InitDbFixture extends \yii\test\InitDbFixture
{
    /**
     * Toggles the DB integrity check.
     * @param bool $check whether to turn on or off the integrity check.
     */
    public function checkIntegrity($check)
    {
        return;
    }
}
