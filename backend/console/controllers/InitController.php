<?php

namespace console\controllers;

use api\modules\exec_module\controllers\ExecController;
use Carbon\Carbon;
use common\exceptions\ValidationException;
use common\IConstant;
use common\models\Account;
use common\models\Contractor;
use common\models\User;
use console\AbstractConsoleController;
use console\modules\install\actions\InstallCollectionAction;
use console\modules\install\actions\InstallSettingAction;
use filsh\yii2\oauth2server\models\OauthClients;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Yii;
use yii\console\Application;

class InitController extends AbstractConsoleController
{
    public function consoleLog($message, $writeLn = false)
    {
        if (\Yii::$app instanceof Application) {
            $this->output->write($message, $writeLn, OutputInterface::OUTPUT_NORMAL);
        }
    }

    public function actionAll()
    {
        $this->actionOAuth();
        $this->actionAdminUser();
    }


    public function actionOAuth()
    {
        /** @var $module \filsh\yii2\oauth2server\Module */
        $module = Yii::$app->getModule('oauth2');

        $clients = [
            [
                'client_id' => 'yandex.alice',
                'client_secret' => Yii::$app->security->generateRandomString(32),
                'redirect_uri' => 'https://social.yandex.net/broker/redirect',
                'grant_types' => 'client_credentials authorization_code password refresh_token password implicit',
                'scope' => null,
                'user_id' => null,
            ],
        ];

        foreach ($clients as $client) {
            $temp = OauthClients::find()
                ->andWhere(['client_id' => $client['client_id']])->one();

            if (!$temp) {
                $temp = new OauthClients();
            }
            $temp->load($client, '');
            if (!$temp->validate()) {
                throw new ValidationException($temp->errors);
            }
            $temp->save();
        }

        return 'success';
    }

    public function actionAdminUser()
    {
        $this->output->write('Init admin-user: ');

        $account = User::find()->andWhere(['username' => '79000000000'])->one();
        if ($account) {
            $account->save();
        } else {
            $account = new User([
                'status_id' => 1,
                'username' => '79000000000',
                'auth_key' => 'auth_key',
                'password_hash' => Yii::$app->security->generatePasswordHash('tester'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null,
            ]);
            if (!$account->validate()) {
                throw new ValidationException($account->errors);
            }
        }

        $account->save();

        $this->output->writeln('<green>OK</green>');
    }
}
