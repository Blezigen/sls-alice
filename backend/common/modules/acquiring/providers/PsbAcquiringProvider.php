<?php

namespace common\modules\acquiring\providers;

use api\exceptions\NotFoundHttpException;
use Carbon\Carbon;
use common\IConstant;
use common\models\OrderPayment;
use common\modules\acquiring\AcquiringData;
use common\modules\acquiring\contracts\IAcquiringProvider;
use common\modules\acquiring\events\CancelPaymentEvent;
use common\modules\acquiring\events\CanDepositPaymentEvent;
use common\modules\acquiring\events\DeniedPaymentEvent;
use common\modules\acquiring\events\DepositPaymentEvent;
use common\modules\acquiring\PaymentAcquiringData;
use common\modules\acquiring\SettingConstant;
use common\modules\filter\functions\InFilter;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Psr\Log\LogLevel;
use yii\helpers\Url;
use yii\log\Logger as YiiLogger;

class PsbAcquiringProvider implements IAcquiringProvider
{
    public const PROVIDER = 'psb';

    public function getComp1()
    {
        return \Yii::$app->user->get(
            SettingConstant::PLUGIN_SECTION,
            SettingConstant::PSB_COMP1,
            null
        );
    }

    public function getComp2()
    {
        return \Yii::$app->user->get(
            SettingConstant::PLUGIN_SECTION,
            SettingConstant::PSB_COMP2,
            null
        );
    }

    public function getUrl()
    {
        return \Yii::$app->user->get(
            SettingConstant::PLUGIN_SECTION,
            SettingConstant::PSB_URL,
            null
        );
    }

    public function getPsbUrl()
    {
        return \Yii::$app->user->get(
            SettingConstant::PLUGIN_SECTION,
            SettingConstant::PSB_URL,
            null
        );
    }

    public function getPsbStatusUrl()
    {
        return \Yii::$app->user->get(
            SettingConstant::PLUGIN_SECTION,
            SettingConstant::PSB_URL_STATUS,
            null
        );
    }

    public function getPsbHost()
    {
        return \Yii::$app->user->get(
            SettingConstant::PLUGIN_SECTION,
            SettingConstant::PSB_HOST,
            null
        );
    }

    public function getPsbTerminal()
    {
        return \Yii::$app->user->get(
            SettingConstant::PLUGIN_SECTION,
            SettingConstant::PSB_TERMINAL,
            null
        );
    }

    public function getPsbMerchName()
    {
        return \Yii::$app->user->get(
            SettingConstant::PLUGIN_SECTION,
            SettingConstant::PSB_TERMINAL,
            null
        );
    }

    public function getPsbMerchant()
    {
        return \Yii::$app->user->get(
            SettingConstant::PLUGIN_SECTION,
            SettingConstant::PSB_MERCHANT,
            null
        );
    }

    public function signData($data, $vars)
    {
        $comp1 = $this->getComp1();
        $comp2 = $this->getComp2();

        $string = '';
        foreach ($vars as $param) {
            if (isset($data[$param]) && strlen($data[$param]) != 0) {
                $string .= strlen($data[$param]).$data[$param];
            } else {
                $string .= '-';
            }
        }
        $key = strtoupper(implode(unpack('H32',
            pack('H32', $comp1) ^ pack('H32', $comp2))));
        $data['p_sign'] = strtoupper(hash_hmac('sha256', $string,
            pack('H*', $key)));

        return array_change_key_case($data, CASE_UPPER);
    }


    public function sendPost($url, $params)
    {
        $host = $this->getPsbHost();

        $stack = HandlerStack::create();
        $stack->push(
            Middleware::log(
                new \yii\psr\Logger(\Yii::$app->log->getLogger(), [
                    LogLevel::ERROR     => YiiLogger::LEVEL_ERROR,
                    LogLevel::CRITICAL  => YiiLogger::LEVEL_ERROR,
                    LogLevel::ALERT     => YiiLogger::LEVEL_ERROR,
                    LogLevel::EMERGENCY => YiiLogger::LEVEL_ERROR,
                    LogLevel::NOTICE    => YiiLogger::LEVEL_WARNING,
                    LogLevel::WARNING   => YiiLogger::LEVEL_WARNING,
                    LogLevel::DEBUG     => YiiLogger::LEVEL_TRACE,
                    LogLevel::INFO      => YiiLogger::LEVEL_TRACE,
                ], "[Guzzle]".__METHOD__),
                new MessageFormatter("{req_body}\n\n{res_body}")
            )
        );

//        $stack->push(Middleware::mapResponse(function (\Psr\Http\Message\ResponseInterface $response) {
//            $responseData = json_decode((string)$response->getBody(), true);
//            return $response;
//        }));

        $stack->push(Middleware::mapResponse(function (
            \Psr\Http\Message\ResponseInterface $response
        ) {
            $contentTypeHeaders = $response->getHeaderLine("Content-type");
            if (!str_contains($contentTypeHeaders, "text/plain")) {
                throw new \Exception("ContentType: $contentTypeHeaders - not allowed");
            }
            return $response;
        }));

        $stack->push(Middleware::mapResponse(function (
            \Psr\Http\Message\ResponseInterface $response
        ) {
            if ($response->getStatusCode() !== 200) {
                throw new \Exception("Response failed status code",
                    $response->getStatusCode());
            }
            return $response;
        }));

        $client = new \GuzzleHttp\Client([
            'handler' => $stack,
        ]);

        $response = $client->post($url, [
            "headers" => [
                'Host: '.$host,
                'User-Agent: '.$_SERVER['HTTP_USER_AGENT'],
                'Accept: application/json',
                'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
            ],
            "body"    => http_build_query($params)
        ]);

        return json_decode($response->getBody(), true);
    }

    public function getPaymentPageData(
        $amount,
        $orderId,
        $email,
        $description = '',
        $backRefUrl = null,
        $notifyUrl = null
    ) {
        $url = $this->getPsbUrl();

        $data = $this->signData([
            'amount'                => number_format("$amount", 2, '.', ''),
            'currency'              => 'RUB',
            'order'                 => $orderId,
            'desc'                  => $description,
            'terminal'              => $this->getPsbTerminal(),
            'trtype'                => '1',
            'email'                 => $email,
            'backref'               => $backRefUrl,
            'addinfo'               => 'Additional information',
            'notify_url'            => $notifyUrl,
            'merchant_notify_email' => 'merchant@mail.test',
        ], [
            'amount',
            'currency',
            'terminal',
            'trtype',
            'backref',
            'order'
        ]);

        return $this->sendPost($url, $data);
    }

    public function getStatusOrder($orderId)
    {
        $url = $this->getPsbStatusUrl();

        $data = $this->signData([
            'order'      => $orderId,
            'terminal'   => $this->getPsbTerminal(),
            'trtype'     => '1',
            'merch_name' => $this->getPsbMerchName(),
            'merchant'   => $this->getPsbMerchant(),
        ], [
            'amount',
            'currency',
            'order',
            'merch_name',
            'merchant',
            'terminal',
            'email',
            'trtype',
            'timestamp',
            'nonce',
            'backref'
        ]);

        return $this->sendPost($url, $data);
    }

    public function generatePaymentLink(AcquiringData $data
    ): PaymentAcquiringData {
        $orderData = $this->getPaymentPageData(
            amount: $data->amount,
            orderId: $data->orderNumber,
            email: $data->email,
            description: $data->description,
            backRefUrl: $data->backUrl,
            notifyUrl: $data->notifyUrl,
        );

        if (!array_key_exists("REF", $orderData)) {
            throw new \Exception("Не удалось получить платёжную ссылку");
        }

        return new PaymentAcquiringData([
            'acquiringOrderId' => $data->orderNumber,
            'paymentUrl'       => $orderData["REF"],
        ]);
    }

    public function validateRequestData($post)
    {
        $P_SIGN = $post['P_SIGN'] ?? null;
        if (!$P_SIGN) {
            throw new \Exception();
        }

        $comp1 = $this->getComp1();
        $comp2 = $this->getComp2();

        $params = array_change_key_case($post, CASE_LOWER);
        $vars = [
            'amount',
            'currency',
            'order',
            'merch_name',
            'merchant',
            'terminal',
            'email',
            'trtype',
            'timestamp',
            'nonce',
            'backref',
            'result',
            'rc',
            'rctext',
            'authcode',
            'rrn',
            'int_ref',
        ];

        $string = '';
        foreach ($vars as $param) {
            if (isset($params[$param]) && strlen($params[$param]) != 0) {
                $string .= strlen($params[$param]).$params[$param];
            } else {
                $string .= '-';
            }
        }
        $key = strtoupper(implode(unpack('H32',
            pack('H32', $comp1) ^ pack('H32', $comp2))));
        $sign = strtoupper(hash_hmac('sha256', $string, pack('H*', $key)));

        if (strcasecmp($params['p_sign'], $sign) == 0) {
            if ((int) $params['result'] == 0 && strcasecmp($params['rc'], '00') == 0) {
                return true;
            }
        }

        return false;
    }

    public function info($acquiringOrderId, $orderId)
    {
        return [];
    }

    public function deposit($acquiringOrderId, $amount)
    {
        return;
    }


    public function processCallbackRequest(\yii\base\Request $request)
    {
        if (!$request->isPost) {
            return;
        }

        $params = $request->post();
        $result = $this->validateRequestData($params);

        if (!$result) {
            return;
        }

        $orderId = $request->post('ORDER');
        $data = $this->getStatusOrder("$orderId");

        if ($data['RCTEXT'] === "Approved"){
            $eventCanDeposit = new CanDepositPaymentEvent([
                "orderId"          => $orderId,
                "acquiringOrderId" => $orderId,
            ]);
            \Yii::$app->trigger(IAcquiringProvider::EVENT_CAN_DEPOSIT,
                $eventCanDeposit);
//            if ($eventCanDeposit->canDeposit()) {
                $event = new DepositPaymentEvent([
                    "orderId"          => $orderId,
                    "acquiringOrderId" => $orderId,
                    "depositAmount"    => $eventCanDeposit->getDepositAmount()
                ]);
                \Yii::$app->trigger(IAcquiringProvider::EVENT_DEPOSITED,
                    $event);
//            }
        } else {
            $event = new CancelPaymentEvent([
                "orderId"          => $orderId,
                "acquiringOrderId" => $orderId,
            ]);
            \Yii::$app->trigger(IAcquiringProvider::EVENT_CANCEL, $event);
        }
    }
}
