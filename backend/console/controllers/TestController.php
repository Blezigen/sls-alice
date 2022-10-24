<?php

namespace console\controllers;

use api\modules\tour_module\exec_actions\OrderAddTempReserveExecAction;
use Carbon\Carbon;
use common\components\services\NavigationService;
use common\components\services\OrderService;
use common\components\services\ShipService;
use common\contracts\IOrderService;
use common\jobs\CheckReservationJob;
use common\jobs\ExpiredContractCompanyNotifyJob;
use common\models\Account;
use common\models\Affiliate;
use common\models\CabinStatistic;
use common\models\Order;
use common\models\OrderPlace;
use common\models\VirtualCabinStatistic;
use common\ReservationService;
use console\AbstractConsoleController;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\ProgressBar;
use yii\console\ExitCode;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

class TestController extends AbstractConsoleController
{
    public function init()
    {
        parent::init();
    }

    public function actionF()
    {
        $login = Account::find()->one();
//        $trans = \Yii::$app->db->beginTransaction();
        $objectDelete = new Affiliate();
        $objectDelete->phone = '13123123';
        $objectDelete->updated_acc = $login->id;
        $objectDelete->save();

        $object = Affiliate::findOne(11);
        $object->phone = '13123123';
        $object->updated_acc = $login->id;
        $object->save();

        $objectDelete->delete();

        dd($object->id);
//        $trans->rollBack();
    }

    public function actionPerm()
    {
        $service = \Yii::$container->get(NavigationService::class);
//        $place = OrderPlace::findOne(1);
//        $place->discount_card_id = 1;
//        $place->discount_category_default_id = 1;
//        $place->discount_category_early_id = null;
//        $place->discount_category_constant_id = null;
//        $place->discount_category_online_id = null;
//        $place->save();
    }

    /**
     * Установка цветов в консоли. Где ключ элемента массива это тег, а значение это цвет.
     *
     * @return OutputFormatterStyle[]
     */
    protected function colors()
    {
        return [
            'order' => new OutputFormatterStyle('red', null, ['blink']),
            'place' => new OutputFormatterStyle('green', null, ['blink']),
            'cabin' => new OutputFormatterStyle('blue', null, ['blink']),
        ];
    }

    public function actionPayment()
    {
        $query = CabinStatistic::find()
            ->byVersion(Carbon::now())
            ->andWhere(['cabin_q.id' => 1]);
//        dd($query->createCommand()->rawSql);
        $data = $query->all();
        dd(ArrayHelper::toArray($data));
    }

    public function actionExec()
    {
//        $data = VirtualCabinStatistic::find()->all();
//        dd(ArrayHelper::toArray($data);
        $exec = new OrderAddTempReserveExecAction('exec', \Yii::$app->controller);
        $data = $exec->run(1, ['102'], '', 1, true);
        exit;
    }

    public function actionFree()
    {
        $manager = new ShipService();
        $data = $manager->analyseCabinByNumber(1, '106');
//        dd(ArrayHelper::toArray($data));
        $manager->freeCabin(1, 1, '106', 'Название', true);
    }

    public function actionTest($start = 0)
    {
        $iterate = 0;
        $dataQuery = Order::find()
            ->with([
                'orderCabins', 'orderCabins.orderPlaces',
                'orderCabins.orderPlaces.placeType',
                'orderCabins.orderPlaces.discountCategoryDefault',
                'orderCabins.orderPlaces.discountCategoryEarly',
                'orderCabins.orderPlaces.discountCategoryConstant',
                'orderCabins.orderPlaces.discountCategoryOnline',
                'orderCabins.orderPlaces.discountCard',
            ])
            ->andWhere(['>=', 'id', $start])
            ->orderBy('id');

        $pagination = new Pagination([
            'pageSize' => 100,
        ]);

        $provider = new ActiveDataProvider([
            'query' => $dataQuery,
            'pagination' => &$pagination,
        ]);

        $pagination->page = 0;
        $models = $provider->getModels();

        $progressBar = new ProgressBar($this->output);
        $progressBar->setFormat('debug');
        $progressBar->setMaxSteps($provider->totalCount);
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            for (
                $provider->pagination->page = 1;
                $provider->pagination->page <= $provider->pagination->pageCount;
                ++$provider->pagination->page
            ) {
                foreach ($models as $order) {
                    ++$iterate;
                    foreach ($order->orderCabins as $orderCabin) {
                        foreach ($orderCabin->orderPlaces as $orderPlace) {
                            $orderPlace->save();
                        }
                        $orderCabin->save();
                    }
                    $order->save();
                    $progressBar->advance();
                }
                $provider->setModels(null);
                $models = $provider->getModels();
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
        }
        $progressBar->finish();

        return ExitCode::OK;
    }

    public function actionWaiting()
    {
        /** @var IOrderService|OrderService $service */
        $service = \Yii::$container->get(IOrderService::class);
        /** @var ReservationService $reservationService */
        $reservationService = \Yii::$container->get(ReservationService::class);

        $result = $service->addTempReserve(3, ['107'], 'Комментарий', 1, true);

        $reservationService->addWaitingList(3, '107', 2,
            Carbon::now()->addMinutes(5), true);
//        dd($result);
    }

    public function actionR()
    {
        \Yii::$app->queue->push(new CheckReservationJob());
    }

    public function actionTest2()
    {
        (new ExpiredContractCompanyNotifyJob())->execute(null);
    }
}
