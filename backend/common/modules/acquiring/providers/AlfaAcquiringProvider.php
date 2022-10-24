<?php

namespace common\modules\acquiring\providers;

use common\modules\acquiring\AcquiringData;
use common\modules\acquiring\contracts\IAcquiringProvider;
use common\modules\acquiring\events\CancelPaymentEvent;
use common\modules\acquiring\events\CanDepositPaymentEvent;
use common\modules\acquiring\events\DeniedPaymentEvent;
use common\modules\acquiring\events\DepositPaymentEvent;
use common\modules\acquiring\PaymentAcquiringData;
use common\modules\filter\functions\InFilter;
use Exception;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use OAuth2\ResponseInterface;
use Psr\Log\LogLevel;
use yii\base\Model;
use yii\httpclient\Client;
use yii\log\Logger;
use yii\log\Logger as YiiLogger;

class AlfaAcquiringProvider extends Model implements IAcquiringProvider
{
    public const ORDER_REGISTER = 0;
    public const ORDER_PRE_AUTHORIZE = 1;
    public const ORDER_DEPOSITED = 2;
    public const ORDER_AUTHORIZE_CANCEL = 3;
    public const ORDER_RETURN = 4;
    public const ORDER_AUTHORIZE = 5;
    public const ORDER_AUTHORIZE_DENIED = 6;


    public const PROVIDER = "alpha";

    private const URL = 'https://web.rbsuat.com/ab/rest/';
    private const REGISTER_METHOD = 'register.do';
    private const GET_ORDER_STATUS_METHOD = 'getOrderStatusExtended.do';

    private const DEPOSIT_METHOD = 'deposit.do';
    private const USERNAME = 'river_sputnik_germes_sputnik-germes.ru-api';
    private const PASSWORD = 'river_sputnik_germes_sputnik-germes.ru*?1';

    public string $username = self::USERNAME;
    public string $password = self::PASSWORD;

    /**
     * @throws Exception
     */
    public function generatePaymentLink(AcquiringData $data): PaymentAcquiringData
    {
        $payload = [
            'orderNumber' => $data->orderNumber,
            'amount' => $data->amount*100,
            'returnUrl' => $data->successUrl,
            'failUrl' => $data->failUrl,
            'dynamicCallbackUrl' => $data->notifyUrl,
            'email' => "khairullin.it@gmail.com"
        ];

        if($data->userId){
            $payload['clientId'] = $data->userId;
        }

        $response = $this->query(self::REGISTER_METHOD, $payload);

        if(!array_key_exists('formUrl', $response) || !array_key_exists('orderId', $response)){
            $this->occurrenceError("");
        }

        return new PaymentAcquiringData([
            'acquiringOrderId' => $response['orderId'],
            'paymentUrl' => $response['formUrl'],
        ]);
    }

    /**
     * @throws Exception
     */
    public function info($acquiringOrderId, $orderId)
    {
        return $this->query(self::GET_ORDER_STATUS_METHOD, [
            'orderId' => $acquiringOrderId,
            'orderNumber' => $orderId,
        ]);
    }

    /**
     * @throws Exception
     */
    public function deposit($acquiringOrderId, $amount)
    {
        return $this->query(self::DEPOSIT_METHOD, [
            'orderId' => $acquiringOrderId,
            'amount' => $amount
        ]);
    }

    /**
     * @throws Exception
     */
    private function query(string $method, array $data = [])
    {
        try {
            $stack = HandlerStack::create();
            $stack->push(
                Middleware::log(
                    new \yii\psr\Logger(\Yii::$app->log->getLogger(), [
                        LogLevel::ERROR => YiiLogger::LEVEL_ERROR,
                        LogLevel::CRITICAL => YiiLogger::LEVEL_ERROR,
                        LogLevel::ALERT => YiiLogger::LEVEL_ERROR,
                        LogLevel::EMERGENCY => YiiLogger::LEVEL_ERROR,
                        LogLevel::NOTICE => YiiLogger::LEVEL_WARNING,
                        LogLevel::WARNING => YiiLogger::LEVEL_WARNING,
                        LogLevel::DEBUG => YiiLogger::LEVEL_INFO,
                        LogLevel::INFO => YiiLogger::LEVEL_INFO,
                    ], "[Guzzle]".__METHOD__),
                    new MessageFormatter("{req_body}\n\n{res_body}")
                )
            );

            $stack->push(Middleware::mapResponse(function (\Psr\Http\Message\ResponseInterface $response) {
                $responseData = json_decode((string)$response->getBody(), true);
                if (array_key_exists("errorCode", $responseData) && $responseData["errorCode"] !== "0"){
                    throw new Exception($responseData["errorMessage"], $responseData["errorCode"]);
                }
                return $response;
            }));

            $stack->push(Middleware::mapResponse(function (\Psr\Http\Message\ResponseInterface $response) {
                $contentTypeHeaders = $response->getHeaderLine("Content-type");
                if (str_contains($contentTypeHeaders, "application/json")){
                    return $response;
                }
                throw new Exception("ContentType: $contentTypeHeaders - not allowed");
            }));

            $stack->push(Middleware::mapResponse(function (\Psr\Http\Message\ResponseInterface $response) {
                if ($response->getStatusCode() === 200){
                    return $response;
                }
                throw new Exception("Response failed status code", $response->getStatusCode());
            }));

            $client = new \GuzzleHttp\Client([
                "base_uri" => static::URL,
                'handler' => $stack,
            ]);

            $response = $client->post($method, [
                "headers" => [
                    "Accept" => "application/json"
                ],
                "form_params" => $this->prepareData($data)
            ]);

            return json_decode((string)$response->getBody(), true);
        } catch (Exception $ex){
            $this->occurrenceError("[{$ex->getCode()}] ".$ex->getMessage());
            throw $ex;
        }
    }

    private function prepareData(array $data): array
    {
        $data['userName'] = $this->username;
        $data['password'] = $this->password;
        return $data;
    }

    /**
     * @throws Exception
     */
    private function occurrenceError($sting)
    {
        throw new Exception("Произошла ошибка с работой 'Альфа Банк', $sting");
    }

    public function processCallbackRequest(\yii\base\Request $request){
        $orderNumber = $request->get('orderNumber');
        $mdOrder = $request->get('mdOrder');

        $info = $this->info($mdOrder, $orderNumber);

        if (!array_key_exists("paymentAmountInfo", $info))
            return;

        if (!array_key_exists("paymentState", $info["paymentAmountInfo"]))
            return;

        if ($info["paymentAmountInfo"]["paymentState"] === "DEPOSITED"){
            $eventCanDeposit = new CanDepositPaymentEvent([
                "orderId"          => $orderNumber,
                "acquiringOrderId" => $mdOrder,
            ]);
            \Yii::$app->trigger(IAcquiringProvider::EVENT_CAN_DEPOSIT, $eventCanDeposit);

//            if ($eventCanDeposit->canDeposit()){
                $event = new DepositPaymentEvent([
                    "orderId"          => $orderNumber,
                    "acquiringOrderId" => $mdOrder,
                    "depositAmount" => $eventCanDeposit->getDepositAmount()
                ]);
                \Yii::$app->trigger(IAcquiringProvider::EVENT_DEPOSITED, $event);
//            }
        }

        if ($info["paymentAmountInfo"]["paymentState"] === "DECLINED"){
            $event = new DeniedPaymentEvent([
                "orderId"          => $orderNumber,
                "acquiringOrderId" => $mdOrder,
            ]);
            \Yii::$app->trigger(IAcquiringProvider::EVENT_DENIED, $event);
        }
    }

}