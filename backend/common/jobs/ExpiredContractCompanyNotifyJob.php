<?php

namespace common\jobs;

use Carbon\Carbon;
use common\IConstant;
use common\models\Collection;
use common\models\Company;
use common\models\Contractor;
use common\notifications\ExpireContractCompanyNotification;
use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class ExpiredContractCompanyNotifyJob extends BaseObject implements JobInterface
{
    public function execute($queue)
    {
        $expiredContractCompanies = Company::find()
            ->andWhere(['<', 'contract_end_at', Carbon::now()])
            ->all();

        $collection = Collection::find()
            ->slug(IConstant::CONTRACTOR_EMPLOYEE)
            ->collection(IConstant::COLLECTION_CONTRACTOR_TYPES)
            ->one();

        $employees = Contractor::find()
            ->andWhere(['contractor_type_cid' => $collection->id])
            ->all();

        foreach ($expiredContractCompanies as $company) {
            $noSendEmployees = array_filter($employees, function (Contractor $entity) use($company) {
                return !$entity->hasNotificationWithNotifiableSlug(Carbon::parse($company->contract_end_at)->format("{$company->id}-".Carbon::DEFAULT_TO_STRING_FORMAT));
            });

            Yii::$app->notifier->send($noSendEmployees, new ExpireContractCompanyNotification($company));
        }
    }
}