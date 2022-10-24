<?php

namespace common\modules\acquiring\services;

use common\modules\acquiring\AcquiringData;
use common\modules\acquiring\contracts\IAcquiringProvider;
use common\modules\acquiring\PaymentAcquiringData;
use Exception;
use yii\base\Model;
use yii\httpclient\Client;

class AlfaAcquiringManager extends Model implements IAcquiringProvider
{
    private const URL = 'https://web.rbsuat.com/ab/rest';

    private const REGISTER_METHOD = 'register.do';
    private const GET_ORDER_STATUS_METHOD = 'getOrderStatus.do';
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
            'amount' => $data->amount,
            'returnUrl' => $data->successUrl,
            'failUrl' => $data->failUrl,
            'email' => $data->email,
            'dynamicCallbackUrl' => $data->notifyUrl
        ];

        if($data->userId){
            $payload['clientId'] = $data->userId;
        }

        $response = $this->query(self::REGISTER_METHOD, $payload);

        if(!array_key_exists('formUrl', $response) || !array_key_exists('orderId', $response)){
            $this->occurrenceError();
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
            $client = new Client();
            $response = $client
                ->createRequest()
                ->setMethod('POST')
                ->setUrl(self::URL.'/'.$method)
                ->setData($this->prepareData($data))
                ->send();
            if (!$response->isOk) {
                $this->occurrenceError();
            }
            return $response->data;
        }catch (Exception){
            $this->occurrenceError();
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
    private function occurrenceError()
    {
        throw new Exception("Произошла ошибка с работой 'Альфа Банк'");
    }
}