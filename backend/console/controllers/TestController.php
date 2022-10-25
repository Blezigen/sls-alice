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
use GuzzleHttp\Client;
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

    public function actionUpdate($userId)
    {
        $notifyData = [
            'ts' => time(),
            'payload' => [
                'user_id' => "$userId",
            ],
        ];
        // y0_AgAAAAAED-OOAAT7owAAAADR0VWMwyENgPPeR8q3oAE2x3h7BXWV1X8
        $skillId = 'c2a5ac3f-2a7e-43a9-9d53-005cfce079ac';
        $client = new Client();
        $result = $client->post("https://dialogs.yandex.net/api/v1/skills/$skillId/callback/discovery", [
            'http_errors' => false,
            'headers' => [
                'Authorization' => 'OAuth y0_AgAAAAAED-OOAAT7owAAAADR0VWMwyENgPPeR8q3oAE2x3h7BXWV1X8',
            ],
            'json' => $notifyData,
        ]);
    }
}
