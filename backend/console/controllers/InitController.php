<?php

namespace console\controllers;

use api\modules\exec_module\controllers\ExecController;
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
//        $this->actionCollection();
//        $this->actionInstall();
        $this->actionOAuth();
        $this->actionPermission();
        $this->actionAdminUser();
        $this->actionBaseDocUser();
        $this->actionAgentUser();
        $this->actionEmployUser();
        $this->actionFizUser();
    }

    public function actionInstall()
    {
        $installController = new InstallSettingAction('setting', Yii::$app->controller);
        $installController->run();
    }

    public function actionCollection()
    {
        $collectionController = new InstallCollectionAction('collection', Yii::$app->controller);
        $collectionController->run();
    }

    public function actionOAuth()
    {
        /** @var $module \filsh\yii2\oauth2server\Module */
        $module = Yii::$app->getModule('oauth2');

        $clients = [
            [
                'client_id' => 'yandex.alice',
                'client_secret' => Yii::$app->security->generateRandomString(32),
                'redirect_uri' => 'https://хайруллин.рус/',
                'grant_types' => 'refresh_token password implicit',
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
//                'status_cid' => null,
                'username' => '79000000000',
                'password_hash' => Yii::$app->security->generatePasswordHash('tester'),
            ]);
            if (!$account->validate()) {
                throw new ValidationException($account->errors);
            }
        }

        $account->save();

        $this->output->writeln('<green>OK</green>');
    }

    public function actionBaseDocUser()
    {
        $this->output->write('Init doc-user: ');
        $account = Account::find()->andWhere(['username' => 'doc'])->one();
        if ($account) {
            $account->role = 'role_doc';
            $account->save();
        } else {
            $account = new Account([
                'status_cid' => null,
                'username' => 'doc',
                'role' => 'role_doc',
                'password_hash' => Yii::$app->security->generatePasswordHash('tester'),
            ]);

            if (!$account->validate()) {
                throw new ValidationException($account->errors);
            }
            $account->save();
        }

        $this->output->writeln('<green>OK</green>');
    }

    public function actionFizUser()
    {
        $this->output->write('Init fiz-user: ');
        $account = Account::find()->andWhere(['username' => '79000000001'])->one();
        if ($account) {
            $account->role = 'role_physical';
            $account->save();
        } else {
            $account = new Account([
                'status_cid' => null,
                'username' => '79000000001',
                'role' => 'role_physical',
                'password_hash' => Yii::$app->security->generatePasswordHash('tester'),
            ]);

            if (!$account->validate()) {
                throw new ValidationException($account->errors);
            }
            $account->save();
        }

        $contractor = Contractor::find()->andWhere(['account_id' => $account->id])->one();

        if (!$contractor) {
            $contractor = new Contractor();
        }

        $contractor->contractorTypeSlug = IConstant::CONTRACTOR_PHYSICAL_PERSON;
        $contractor->account_id = $account->id;
        $contractor->email = 'example@test.domain';
        if (!$contractor->validate()) {
            throw new ValidationException($contractor->errors);
        }
        $contractor->save();

        $this->output->writeln('<green>OK</green>');
    }

    public function actionAgentUser()
    {
        $this->output->write('Init agent-user: ');
        $account = Account::find()->andWhere(['username' => '79000000002'])->one();
        if ($account) {
            $account->role = 'role_agent';
            $account->save();
        } else {
            $account = new Account([
                'status_cid' => null,
                'username' => '79000000002',
                'role' => 'role_agent',
                'password_hash' => Yii::$app->security->generatePasswordHash('tester'),
            ]);

            if (!$account->validate()) {
                throw new ValidationException($account->errors);
            }
            $account->save();
        }

        $contractor = Contractor::find()->andWhere(['account_id' => $account->id])->one();

        if (!$contractor) {
            $contractor = new Contractor();
        }

        $contractor->contractorTypeSlug = IConstant::CONTRACTOR_AGENT;
        $contractor->account_id = $account->id;
        $contractor->region_id = 16;
        $contractor->city_id = 1;
        $contractor->email = 'example@test.domain';
        if (!$contractor->validate()) {
            throw new ValidationException($contractor->errors);
        }
        $contractor->save();

        $this->output->writeln('<green>OK</green>');
    }

    public function actionEmployUser()
    {
        $this->output->write('Init employ-user: ');
        $account = Account::find()->andWhere(['username' => '79000000003'])->one();

        if ($account) {
            $account->role = 'role_employ';
            $account->save();
        } else {
            $account = new Account([
                'status_cid' => null,
                'username' => '79000000003',
                'role' => 'role_employ',
                'password_hash' => Yii::$app->security->generatePasswordHash('tester'),
            ]);

            if (!$account->validate()) {
                throw new ValidationException($account->errors);
            }
            $account->save();
        }

        $contractor = Contractor::find()->andWhere(['account_id' => $account->id])->one();

        if (!$contractor) {
            $contractor = new Contractor();
        }

        $contractor->contractorTypeSlug = IConstant::CONTRACTOR_EMPLOYEE;
        $contractor->account_id = $account->id;
        $contractor->email = 'example@test.domain';
        if (!$contractor->validate()) {
            throw new ValidationException($contractor->errors);
        }
        $contractor->save();

        $this->output->writeln('<green>OK</green>');
    }

    public function hideGroupedAttribute(string $who, string $group, array $attributes)
    {
        $groupPolicyName = "hide_{$group}-attributes";
        $policies = [];
        foreach ($attributes as $attribute) {
            $policies[] = "hide_$attribute-attribute";
            \Yii::$app->permission->addPolicy("hide_$attribute-attribute", '*', '*', '*', 'showAttribute', '*', $attribute, 'deny');
        }

        foreach ($policies as $policy) {
            \Yii::$app->permission->addGroupingPolicy([$groupPolicyName, $policy]);
        }

        \Yii::$app->permission->addGroupingPolicy([$who, $groupPolicyName]);
    }

    public function roleAllowExec($role, $exec)
    {
        if (!Yii::$app->permission->hasGroupingPolicy($role, "allow-exec_module-exec-{$exec}")) {
            Yii::$app->permission->addGroupingPolicy($role, "allow-exec_module-exec-{$exec}");
        }
    }

    public function actionPermission()
    {
        $progressBar = new ProgressBar($this->output);
        $conrtoller = new ExecController('exec', Yii::$app->module, []);
        $actions = $conrtoller->actions();
        $progressBar->setMaxSteps(count($actions));
        $progressBar->setMessage('Инициализация exec прав');
        $progressBar->setFormat("%message:40s%: [%bar%] %current:6s%/%max:6s% %percent:3s%% %elapsed:6s% %memory:6s%\n\n");

        Yii::$app->permission->addPolicy('allow-exec_module-all', 'exec_module', 'exec', '*', '*', '*', '*', 'allow');
        Yii::$app->permission->addPolicy('deny-exec_module-all', 'exec_module', 'exec', '*', '*', '*', '*', 'deny');

        foreach ($actions as $action => $data) {
//            $progressBar->setMessage("Действие: ".$action);
            $progressBar->advance();
            if (!Yii::$app->permission->hasPolicy("deny-exec_module-exec-{$action}", 'exec_module', 'exec', "{$action}", '*', '*', '*', 'deny')) {
                Yii::$app->permission->addPolicy("deny-exec_module-exec-{$action}", 'exec_module', 'exec', "{$action}", '*', '*', '*', 'deny');
            }
            if (!Yii::$app->permission->hasPolicy("allow-exec_module-exec-{$action}", 'exec_module', 'exec', "{$action}", '*', '*', '*', 'allow')) {
                Yii::$app->permission->addPolicy("allow-exec_module-exec-{$action}", 'exec_module', 'exec', "{$action}", '*', '*', '*', 'allow');
            }
        }

        \Yii::$app->permission->addPolicy('allow_view_api_doc', '*', '*', '*', 'view_doc', '*', '*', 'allow');
        \Yii::$app->permission->addPolicy('allow_all', '*', '*', '*', '*', '*', '*', 'allow');
        \Yii::$app->permission->addPolicy('cancel-order-full_payment', '*', '*', '*', 'has_permission', 'OrderService', 'cancel-order-full_payment', 'allow');

        \Yii::$app->permission->addGroupingPolicy(['role_admin', 'role_basic']);
        \Yii::$app->permission->addGroupingPolicy(['role_guest', 'role_basic']);
        \Yii::$app->permission->addGroupingPolicy(['role_employ', 'role_basic']);
        \Yii::$app->permission->addGroupingPolicy(['role_user', 'role_basic']);
        \Yii::$app->permission->addGroupingPolicy(['console', 'role_admin']);

//        \Yii::$app->permission->addGroupingPolicy(['role_employ', 'deny-exec_module-all']);
//        \Yii::$app->permission->addGroupingPolicy(['role_user', 'deny-exec_module-all']);
        \Yii::$app->permission->addGroupingPolicy(['role_employ', 'cancel-order-full_payment']);

        $this->hideGroupedAttribute('role_basic', 'update', [
            'updated_at',
            'updated_acc',
        ]);

        $this->hideGroupedAttribute('role_basic', 'create', [
            'created_at',
            'created_acc',
        ]);

        $this->hideGroupedAttribute('role_basic', 'delete', [
            'deleted_at',
            'deleted_acc',
        ]);

        $this->roleAllowExec('role_basic', 'Acceptance.sendCode');
        $this->roleAllowExec('role_basic', 'Acceptance.getAcceptToken');

        $this->roleAllowExec('role_employ', 'Order.addTempReserve');
        $this->roleAllowExec('role_employ', 'Order.cancelTempReserve');
        $this->roleAllowExec('role_employ', 'Order.createFromTempReserve');
        $this->roleAllowExec('role_employ', 'Order.changeManager');
        $this->roleAllowExec('role_employ', 'Order.addCabin');
        $this->roleAllowExec('role_employ', 'Order.deleteCabin');
        $this->roleAllowExec('role_employ', 'Order.cancel');
        $this->roleAllowExec('role_employ', 'Order.print');
        $this->roleAllowExec('role_employ', 'Order.addPlace');
        $this->roleAllowExec('role_employ', 'Order.deletePlace');
        $this->roleAllowExec('role_employ', 'Order.addExcursion');
        $this->roleAllowExec('role_employ', 'Order.deleteExcursion');
        $this->roleAllowExec('role_employ', 'Order.touristPlaceAssign');
        $this->roleAllowExec('role_employ', 'Order.clearTouristPlaceAssign');
        $this->roleAllowExec('role_employ', 'Order.addInsurance');
        $this->roleAllowExec('role_employ', 'Order.addDiscountCardToOrderPlace');
        $this->roleAllowExec('role_employ', 'Order.clearDiscountCardToOrderPlace');
        $this->roleAllowExec('role_employ', 'Tourist.print');
        $this->roleAllowExec('role_employ', 'Payments.createPayment');
        $this->roleAllowExec('role_employ', 'Reserve.dropWaiting');
        $this->roleAllowExec('role_employ', 'Reserve.addWaiting');
        $this->roleAllowExec('role_employ', 'Statistic.city');
        $this->roleAllowExec('role_employ', 'Statistic.contractor');
        $this->roleAllowExec('role_employ', 'Statistic.agent');
        $this->roleAllowExec('role_employ', 'Statistic.management');
        $this->roleAllowExec('role_employ', 'Statistic.receipt');
        $this->roleAllowExec('role_employ', 'Statistic.employ');
        $this->roleAllowExec('role_employ', 'Statistic.physical');
        $this->roleAllowExec('role_employ', 'Statistic.tour');
        $this->roleAllowExec('role_employ', 'Ship.addCabins');
        $this->roleAllowExec('role_employ', 'Ship.lockCabins');
        $this->roleAllowExec('role_employ', 'Ship.unlockCabins');
        $this->roleAllowExec('role_employ', 'Ship.splitCabin');
        $this->roleAllowExec('role_employ', 'Ship.getCabinsStatuses');

        \Yii::$app->permission->addGroupingPolicy(['role_admin', 'allow_all']);
        \Yii::$app->permission->addGroupingPolicy(['role_admin', 'allow-exec_module-all']);
        \Yii::$app->permission->addGroupingPolicy(['role_doc', 'allow_view_api_doc']);
    }
}
