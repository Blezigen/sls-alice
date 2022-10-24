<?php

namespace doc\modules\swagger\commands;

use console\AbstractConsoleController;
use Yii;

class ChangeController extends AbstractConsoleController
{
    /**
     * @var mixed
     */
    public $date = null;
    /**
     * @var mixed
     */
    public $version = 'auto';

    public function options($actionID)
    {
        return [
            'date',
            'version',
        ];
    }

    public function actionAdd($description)
    {
        Yii::$app->swagger->addChange($description, $this->version, $this->date);
        Yii::$app->swagger->saveCanges();
    }
}
