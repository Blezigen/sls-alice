<?php

namespace common\modules\acceptance\actions;

use common\actions\AbstractExecAction;
use common\behaviors\PhoneFormatBehavior;
use common\exceptions\ValidationException;
use common\modules\acceptance\AcceptanceServices;
use common\modules\acceptance\contracts\IAcceptanceManager;
use yii\base\DynamicModel;

/**
 * Поучение токена подтверждения для использования в других методах.
 */
class GetAcceptTokenOnConfirmCodeExecAction extends AbstractExecAction
{
    public function __params()
    {
        return [
            'phone' => 'Телефон на который был передан код. Допускаются любой ввод, главное вводить 11-цифр',
            'code' => 'Код подтверждения переданный на телефон',
            'channel' => 'Канал по которому был передан код подтверждения [call|sms]',
        ];
    }

    public function __example()
    {
        $channels = ['call', 'sms'];
        $phones = [
            '79274361277',
            '+79274361277',
            '+7(927)43-61-277',
            '+7(927)436-12-77',
            '89274361277',
        ];

        return [
            [
                '_type' => 'regex',
                'key' => '/phone/',
                'type' => 'string',
                'value' => $phones[random_int(0, count($phones) - 1)],
            ],
            [
                '_type' => 'regex',
                'key' => '/channel/',
                'type' => 'string',
                'value' => $channels[random_int(0, count($channels) - 1)],
            ],
            [
                '_type' => 'regex',
                'key' => '/code/',
                'type' => 'string',
                'value' => (string) random_int(1000, 9999),
            ],
        ];
    }

    public function run(string $phone, string $code, $channel = IAcceptanceManager::SMS)
    {
        try {
            $model = DynamicModel::validateData([
                'phone' => $phone,
            ], [
                [['phone'], 'string'],
                [['phone'], 'required'],
            ]);

            $model->attachBehaviors([
                ['class' => PhoneFormatBehavior::class], // чистка номера телефона
            ]);

            if (!$model->validate()) {
                throw new ValidationException($model->errors);
            }

            /** @var AcceptanceServices $manager */
            $manager = \Yii::$app->get(IAcceptanceManager::class);

            return $manager->getAcceptTokenOnCode($model->phone, $code, $channel);
        } catch (\Throwable $ex) {
            throw $ex;
        }
    }
}
