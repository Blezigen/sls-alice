<?php

namespace common\modules\acceptance;

use Carbon\Carbon;
use common\exceptions\ValidationException;
use common\modules\acceptance\contracts\IAcceptanceProvider;
use common\modules\acceptance\models\Acceptance;
use common\modules\acceptance\models\SingleUser;
use common\modules\acceptance\notifications\SendSmsCodeInitiator;
use common\modules\sender\SenderServices;
use yii\base\Model;

abstract class AbstractAcceptanceProvider extends Model implements IAcceptanceProvider
{
    public $generateAttemptMax = 5;

    public $codeAttemptMax = 5;

    public $delay = 60;

    abstract public function getChannel();

    public function createAcceptance($phone, $code, $generateAttempt = 1)
    {
        $temp = new Acceptance([
            'accept_token' => null,
            'channel' => $this->getChannel(),
            'phone' => $phone,
            'generate_attempt_count' => $generateAttempt,
            'code' => $code,
            'code_attempt_count' => 0,
            'used_at' => null,
            'retry_at' => Carbon::now()->addSeconds($this->delay)
                ->format(Carbon::DEFAULT_TO_STRING_FORMAT),
            'expired_at' => Carbon::now()->addDay()
                ->format(Carbon::DEFAULT_TO_STRING_FORMAT),
        ]);

        if (!$temp->validate()) {
            throw new ValidationException($temp->errors);
        }

        $temp->save();

        return $temp;
    }

    public function hasAcceptance($phone, $accept_token): bool
    {
        $temp = Acceptance::find()
            ->phone($phone)
            ->andWhere(['accept_token' => $accept_token])
            ->one();

        return $temp !== null;
    }

    public function getAcceptance($phone): ?Acceptance
    {
        /** @var Acceptance $temp */
        $temp = Acceptance::find()
            ->channel($this->getChannel())
            ->phone($phone)
            ->andWhere(['is', 'accept_token', null])
            ->notUsed()
            ->andWhere([
                '>',
                'created_at',
                Carbon::now()->startOfDay()
                    ->format(Carbon::DEFAULT_TO_STRING_FORMAT),
            ])
            ->orderBy('generate_attempt_count DESC')
            ->one();

        return $temp;
    }

    public function useAcceptance($phone, $accept_token)
    {
        $temp = Acceptance::find()
            ->phone($phone)
            ->andWhere(['accept_token' => $accept_token])
            ->notUsed()
            ->one();

        if (!$temp) {
            throw new \Exception('Код подтверждения не опознан');
        }

        $temp->used_at = Carbon::now()
            ->format(Carbon::DEFAULT_TO_STRING_FORMAT);
        $temp->save();

        return true;
    }

    public function sendCode($phone)
    {
        $acceptance = $this->getAcceptance($phone);

        if (!$acceptance) {
            /** @var SenderServices $sender */
            $sender = \Yii::$app->get('sender');
            $sender->notify(new SingleUser([
                'phone' => $phone,
            ]), new SendSmsCodeInitiator());

            return true;
        }

        if (!Carbon::parse($acceptance->retry_at)->lt(Carbon::now())) {
            throw new \Exception('Попробуйте позднее');
        }

        if ($acceptance->accept_token !== null) {
            throw new \Exception('У вас имеется не использованный код.');
        }

        if ($acceptance->generate_attempt_count >= $this->generateAttemptMax) {
            throw new \Exception('Превышено кол-во генерации кода');
        }

        /** @var SenderServices $sender */
        $sender = \Yii::$app->get('sender');
        $sender->notify(new SingleUser([
            'phone' => $phone,
        ]), new SendSmsCodeInitiator([
            'generateAttempt' => $acceptance->generate_attempt_count + 1,
        ]));

        return true;
    }

    public function getAcceptOnCode($phone, $code)
    {
        $acceptance = $this->getAcceptance($phone);

        if (!$acceptance) {
            throw new \Exception('Ошибочный код');
        }

        if ($acceptance->code_attempt_count >= $this->codeAttemptMax) {
            throw new \Exception('Превышено кол-во попыток ввода кода подтверждения');
        }

        if ($acceptance->code !== $code) {
            ++$acceptance->code_attempt_count;
            $acceptance->save();
            throw new \Exception('Код не верен, попробуйте ввести его ещё раз');
        }

        if ($acceptance->accept_token) {
            throw new \Exception('Не найден телефон для подтверждения');
        }

        $acceptance->accept_token = md5($acceptance->created_at
            . $acceptance->phone . $acceptance->code);
        $acceptance->save();

        return $acceptance->accept_token;
    }
}
