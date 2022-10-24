<?php

namespace common\modules\acceptance\actions;

use common\actions\AbstractExecAction;
use common\behaviors\PhoneFormatBehavior;
use common\exceptions\ValidationException;
use common\modules\acceptance\AcceptanceServices;
use common\modules\acceptance\contracts\IAcceptanceManager;
use yii\base\DynamicModel;

/**
 * Передача кода подтверждения через канал.
 */
class SendConfirmCodeCodeExecAction extends AbstractExecAction
{
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
                'value' => random_int(1000, 9999),
            ],
        ];
    }

    /**
     * @param  string  $phone Телефон, который следует подтвердить. Допускаются любой ввод, главное вводить 11-цифр
     * @param  string  $channel Канал по которому передан код [call|sms]
     *
     * @return string
     * @throws ValidationException
     * @throws \yii\base\InvalidConfigException
     */
    public function run(string $phone, string $channel = IAcceptanceManager::SMS)
    {
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
        $result = $manager->sendCode($model->phone, $channel);

        return 'Код был успешно отправлен';
    }
}
