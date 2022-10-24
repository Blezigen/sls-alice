<?php

namespace console\controllers;

use common\models\Account;
use common\models\Contractor;

class UserController extends \yii\console\Controller
{
    public const NAME = 'Админ';
    public const EMAIL = 'i@rdbx.ru';
    public const PHONE = '79046784532';

    public function actionInit()
    {
        $contractor = Contractor::findOne(1);
//        $contractor->setSetting("default", "ads", 10);

        $account = new Account();
        $account->username = 'admin';
        $account->setPassword('tester');
        if (!$account->validate()) {
            dd($account->errors);
        }
        $account->save();
    }
}
