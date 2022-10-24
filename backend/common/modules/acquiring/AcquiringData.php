<?php


namespace common\modules\acquiring;


use yii\base\Model;

class AcquiringData extends Model
{
    /** @var string Номер заказа внутри системы */
    public string $orderNumber;

    /** @var null|string Ид пользователя который сформировал оплату */
    public ?string $userId;

    /** @var string Куда перейти в случае успешной оплаты */
    public string $successUrl;

    /** @var string Куда перейти в случае неуспешной оплаты */
    public string $failUrl;

    /** @var string Куда отправить callback */
    public string $notifyUrl;

    /** @var string Куда отправить backUrl */
    public string $backUrl;

    /** @var string|int Цена для инициализации оплаты в копейках */
    public string|int $amount;

    /** @var string|null Электронная почта для отправки чека */
    public string|null $email;

    /** @var string|null Описание заказа */
    public string|null $description;

    public array $params = [];

}