<?php

namespace common;

use Carbon\Carbon;
use common\components\services\OrderService;
use common\events\CanReadAttributesEvent;
use common\events\OrderCabinFreeEvent;
use common\models\Order;
use common\models\OrderCabin;
use common\models\OrderPayment;
use common\models\OrderPlace;
use common\modules\acquiring\contracts\IAcquiringProvider;
use common\modules\acquiring\events\CancelPaymentEvent;
use common\modules\acquiring\events\CanDepositPaymentEvent;
use common\modules\acquiring\events\DeniedPaymentEvent;
use common\modules\acquiring\events\DepositPaymentEvent;
use ReflectionClass;
use Yii;
use yii\base\BootstrapInterface;
use yii\base\Event;
use yii\db\BaseActiveRecord;
use yii\helpers\ArrayHelper;

class Bootstrap implements BootstrapInterface
{
    public function bootstrap($app)
    {
    }
}
