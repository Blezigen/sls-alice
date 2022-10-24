<?php

namespace common\components\services;

use common\IConstant;
use common\models\Collection;
use common\models\Order;
use common\models\OrderPayment;
use common\modules\acquiring\SettingConstant;
use yii\base\Exception;

class PSBService
{


    /**
     * @param $orderPayments
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getPayments($orderPayments)
    {
        $comp1 = $this->getComp1();
        $comp2 = $this->getComp2();
        $psb_url = $this->getPsbUrl();

        $data = [
                'amount' => number_format("{$orderPayments->payment_amount}", 2, '.', ''),
                'currency' => 'RUB',
                'order' => "{$orderPayments->order_id}",
                'desc' => $orderPayments->description,
                'terminal' => $this->getPsbTerminal(),
                'trtype' => '1',
                'merch_name' => $this->getPsbMerchName(),
                'merchant' => $this->getPsbMerchant(),
                'email' => 'cardholder@mail.test',
                'timestamp' => gmdate('YmdHis'),
                'nonce' => bin2hex(random_bytes(16)),
                'backref' => 'https://river.germes.rdbx.dev/api/acquiring/psb/payback/' . $orderPayments->id,
                'notify_url' => 'https://river.germes.rdbx.dev/api/acquiring/psb/payback',
                'cardholder_notify' => 'EMAIL',
                'merchant_notify' => 'EMAIL',
                'merchant_notify_email' => 'merchant@mail.test',
            ];

        $vars =
                ['amount', 'currency', 'order', 'merch_name', 'merchant', 'terminal', 'email', 'trtype', 'timestamp', 'nonce', 'backref',
                ];
        $string = '';
        foreach ($vars as $param) {
            if (isset($data[$param]) && strlen($data[$param]) != 0) {
                $string .= strlen($data[$param]) . $data[$param];
            } else {
                $string .= '-';
            }
        }
        $key = strtoupper(implode(unpack('H32', pack('H32', $comp1) ^ pack('H32', $comp2))));
        $data['p_sign'] = strtoupper(hash_hmac('sha256', $string, pack('H*', $key)));

        $str = "<form id='payment_form' action='" . $psb_url . "' method = 'POST'>";
        foreach ($data as $param => $value) {
            $str .= "<input type='hidden' name='" . strtoupper($param) . "' value='" . $value . "'/>";
        }
        $str .= "<input type='submit' name='SUBMIT' value='Перейти к оплате' />";
        $str .= '</form>';
        $str .= "Если не произошло автоматического перенаправления, нажмите на кнопку 'Перейти к оплате'";
        $str .= "<script type='text/javascript'>document.getElementById('payment_form').submit();</script>";

        return $str;
    }

    public function eventPost($request)
    {

    }
}
