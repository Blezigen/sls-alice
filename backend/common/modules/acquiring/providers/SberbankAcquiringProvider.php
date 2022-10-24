<?php

namespace common\modules\acquiring\providers;

use common\modules\acquiring\AcquiringData;
use common\modules\acquiring\contracts\IAcquiringProvider;
use common\modules\acquiring\PaymentAcquiringData;
use Voronkovich\SberbankAcquiring\Client;
use Voronkovich\SberbankAcquiring\Currency;
use Voronkovich\SberbankAcquiring\HttpClient\HttpClientInterface;
use yii\base\Model;

class SberbankAcquiringProvider extends Model implements IAcquiringProvider
{

    const PROVIDER = "sberbank";

    public string $username;
    public string $password;
    public bool $test;

    protected function client()
    {
        \Yii::debug("SberbankAcquiringProvider($this->username, $this->password, $this->test)");
        return new Client([
            'userName'   => $this->username,
            'password'   => $this->password,
            'language'   => 'ru',
            'currency'   => Currency::RUB,
            'apiUri'     => $this->test ? Client::API_URI_TEST
                : Client::API_URI,
            'httpMethod' => HttpClientInterface::METHOD_POST,
            'httpClient' => new \Voronkovich\SberbankAcquiring\HttpClient\GuzzleAdapter(new \GuzzleHttp\Client([
                'verify'          => false,
            ]))
        ]);
    }

    public function generatePaymentLink(AcquiringData $data): PaymentAcquiringData
    {
        $result = $this->client()->registerOrderPreAuth(
            $data->orderNumber."_".date("YmdHis"),
            $data->amount,
            $data->successUrl,
            [
                'currency'   => Currency::RUB,
                "failUrl"    => $data->failUrl,
                "email"      => $data->email,
                //                "orderBundle" => $orderBundle,
                "jsonParams" => [
                    "type" => "order",
                    "order_id" => $data->orderNumber,
                    "user_id" => $data->userId,
                ],
            ]
        );

        return new PaymentAcquiringData([
            "acquiringOrderId" => $result['orderId'],
            "paymentUrl"       => $result['formUrl']
        ]);
    }

    public function info($acquiringOrderId, $orderNumber)
    {
        return $this->client()->getOrderStatus(
            $acquiringOrderId,
            [
                "orderNumber" => $orderNumber
            ]);
    }

    public function deposit($acquiringOrderId, $amount)
    {
        $this->client()->deposit($acquiringOrderId, $amount);
    }

    public function processCallbackRequest(\yii\base\Request $request){}

}