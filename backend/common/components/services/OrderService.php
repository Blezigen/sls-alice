<?php

namespace common\components\services;

use api\exceptions\NotFoundHttpException;
use Carbon\Carbon;
use common\contracts\cabins\IGetterCabinNumber;
use common\contracts\IOrderService;
use common\exceptions\OrderServiceException;
use common\exceptions\ValidationException;
use common\IConstant;
use common\models\Account;
use common\models\Cabin;
use common\models\Collection;
use common\models\Contractor;
use common\models\DiscountCard;
use common\models\Excursion;
use common\models\IdentityDocument;
use common\models\Order;
use common\models\OrderCabin;
use common\models\OrderExcursion;
use common\models\OrderPayment;
use common\models\OrderPlace;
use common\models\Tour;
use common\models\VirtualCabin;
use common\modules\order\SettingConstant;
use common\queries\OrderPlaceQuery;
use Exception;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Throwable;
use Yii;
use yii\base\Event;
use yii\db\ActiveRecord;
use yii\db\QueryBuilder;
use yii\db\StaleObjectException;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

class OrderService implements IOrderService
{
    public function reserveAddDaysSetting()
    {
        return Yii::$app->user->get(
            SettingConstant::PLUGIN_SECTION,
            SettingConstant::AUTO_RESERVATION_DAYS,
            4
        );
    }

    public function maxDiscount()
    {
        return Yii::$app->user->get(
            SettingConstant::PLUGIN_SECTION,
            SettingConstant::MAX_DISCOUNT,
            30
        );
    }

    public function maxCountReceiptNotPayment()
    {
        return Yii::$app->user->get(
            SettingConstant::PLUGIN_SECTION,
            SettingConstant::MAX_COUNT_RECEIPT_NOT_PAYMENT,
            2
        );
    }

    public function maxCountReserveNotPayment()
    {
        return Yii::$app->user->get(
            SettingConstant::PLUGIN_SECTION,
            SettingConstant::MAX_COUNT_RESERVE_NOT_PAYMENT,
            2
        );
    }

    public function maxCountReserveCabin()
    {
        return Yii::$app->user->get(
            SettingConstant::PLUGIN_SECTION,
            SettingConstant::MAX_COUNT_RESERVE_CABIN,
            6
        );
    }

    public function countExpiredDaysForFilter()
    {
        return Yii::$app->user->get(
            SettingConstant::PLUGIN_SECTION,
            SettingConstant::COUNT_EXPIRED_DAYS_FOR_FILTER,
            3
        );
    }

    /**
     * @param $tourId  - идентификатор тура
     * @param $numbers  - номера кают (не идентификаторы)
     * @param $comment  - комментарий
     * @param $contractorId  - кто будет оплачивать
     * @param  bool  $save  - сохранить результат
     *
     * @throws NotFoundHttpException|ValidationException|Throwable
     */
    public function addTempReserve(
        $tourId,
        $numbers,
        $comment,
        $contractorId,
        bool $save = false
    ): int {
        /** @var Account $identity */
        $identity = Yii::$app->user->identity;

        if ($identity->getReserveCount() >= $this->maxCountReserveNotPayment()) {
            throw new OrderServiceException('Превышено количество временных броней!');
        }

//        dd($identity->id, $identity->getReceiptCount(), $identity->getReserveCount());

        if (!Tour::find()->byId($tourId)->exists()) {
            throw new NotFoundHttpException("Tour ($tourId) not found");
        }

        $contractor = Contractor::findOne($contractorId);

        if (!$contractor) {
            throw new NotFoundHttpException("Contractor ($contractorId) not found");
        }

        $collection = Collection::find()
            ->collection(IConstant::COLLECTION_CONTRACTOR_TYPES)
            ->slug(IConstant::CONTRACTOR_AGENT)
            ->one();

        if ($contractor->contractor_type_cid == $collection->id) {
            $company = $contractor->company;
            $contract_start_at = Carbon::parse($company->contract_start_at);
            $contract_end_at = Carbon::parse($company->contract_end_at);
            if (Carbon::now() < $contract_start_at
                || $contract_end_at < Carbon::now()
            ) {
                throw new Exception('Истёк срок действия договора!');
            }
        }

        if ($contractor->blacklist) {
            throw new Exception("Contractor ($contractorId) blocked");
        }

        $collection = Collection::find()->slug(IConstant::ORDER_TYPE_TEMP)
            ->one();

        if ($collection == null) {
            throw new InternalErrorException('Collection (slug = ' . IConstant::ORDER_TYPE_TEMP . ') not found');
        }

        $order = new Order();
        $order->tour_id = $tourId;
        $order->comment = $comment;
        $order->contractor_id = $contractorId;
        $order->order_type_cid = $collection->id;
        $order->reserve_end_date = Carbon::now()
            ->addDays($this->reserveAddDaysSetting());

        if (!$order->validate()) {
            throw new ValidationException($order->errors);
        }

        /** @var Account $identity */
        $identity = Yii::$app->user->identity;
        if ($identity->getCabinCount(count($numbers)) > $this->maxCountReserveCabin()) {
            throw new OrderServiceException('Превышено количество количество забронированных кают!');
        }

        $order->on(ActiveRecord::EVENT_AFTER_INSERT, function (Event $event) use ($numbers) {
            /** @var Order $order */
            $order = $event->sender;

            $event->data = [];
            foreach ($numbers as $number) {
                $event->data[] = $this->addCabin($order, $number);
            }

            $event->handled = true;
        });

        if ($save) {
            $order->save();
        }

        return $order->id;
    }

    /**
     * @param $reserveId  - идентификатор резервации
     * @param  bool  $save  - сохранить результат
     *
     * @throws NotFoundHttpException
     */
    public function cancelTempReserve($reserveId, bool $save = false): void
    {
        $order = $this->getOrderById($reserveId);

        if ($save) {
            $order->softDelete();
        }
    }

    /**
     * @param $orderId  - идентификатор заказа
     * @param  bool  $save  - сохранить результат
     *
     * @throws NotFoundHttpException
     */
    public function cancelOrder($orderId, bool $save = false)
    {
        $order = $this->getOrderById($orderId);

        $canDeleteOrderWithAnyPayment = Yii::$app->user->enforceYii('has_permission', 'OrderService', 'cancel-order-full_payment');

        $hasPayment = false;

        if ($order->orderPayments) {
            foreach ($order->orderPayments as $payments) {
                if ($payments->status->slug == IConstant::PAYMENT_STATUS_PAYED || $payments->status->slug == IConstant::PAYMENT_STATUS_PARTIALLY_PAYED || $payments->status->slug == IConstant::PAYMENT_STATUS_OVER_PAYED) {
                    $hasPayment = true;
                    break;
                }
            }
        }

        if ($hasPayment && !$canDeleteOrderWithAnyPayment) {
            throw new OrderServiceException('Данный пользователь не имеет права удалять заказ с проплатой!');
        }

        if ($save) {
            $order->softDelete();
        }
    }

    /**
     * @throws Throwable
     * @throws ValidationException
     */
    public function createFromOrderTempReserve(
        Order $order,
        bool $save = false
    ): string|int {
        if ($order->orderTypeSlug !== IConstant::ORDER_TYPE_TEMP) {
            throw new OrderServiceException('Повторная попытка создать заказ!', -400, []);
        }

        /** @var Account $identity */
        $identity = Yii::$app->user->identity;

        if ($identity->getReceiptCount() >= $this->maxCountReceiptNotPayment()) {
            throw new OrderServiceException('Превышено количество неоплаченных счётов!');
        }

        $order->orderTypeSlug = IConstant::ORDER_TYPE_GENERAL;
        $order->reserve_end_date = null;
        $order->validate();

        if ($save) {
            $order->save();
        }

        return $order->id;
    }

    /**
     * @param $reserveId  - идентификатор резервации
     * @param  bool  $save  - сохранить результат
     *
     * @throws NotFoundHttpException|Throwable
     */
    public function createFromTemp($reserveId, bool $save = false): string|int
    {
        $order = $this->getOrderById($reserveId);

        return $this->createFromOrderTempReserve($order, $save);
    }

    public function addCabin(Order $order, string|int $number)
    {
        /** @var Account $identity */
        $identity = Yii::$app->user->identity;
        if ($identity->getCabinCount(1) > $this->maxCountReserveCabin()) {
            throw new OrderServiceException('Превышено количество количество забронированных кают!');
        }

        Yii::debug('Cabin count:' . $identity->getCabinCount(), __METHOD__);

        /** @var ShipService $shipService */
        $shipService = Yii::$container->get(ShipService::class);

        $tour = $order->tour;
        $ship = $tour->ship;

        if ($order->deleted_at !== null) {
            throw new OrderServiceException('Нельзя добавить каюту к аннулированному заказу');
        }

        $paymentStatuses = [
            IConstant::PAYMENT_STATUS_PAYED,
            IConstant::PAYMENT_STATUS_OVER_PAYED,
            IConstant::PAYMENT_STATUS_PARTIALLY_PAYED,
        ];

        if (in_array($order->paymentStatus?->slug ?? '', $paymentStatuses)) {
            throw new OrderServiceException('Нельзя добавить каюту к оплаченному заказу');
        }

        /** @var VirtualCabin $virtual */
        $virtual = VirtualCabin::find()
            ->andWhere(['number' => $number, 'tour_id' => $tour->id])
            ->one();

        if ($virtual) {
            $result = $shipService->analyseCabin($virtual->cabin);

            if (!$result->cabinSellingInTour($tour)) {
                throw new \Redbox\JsonRpc\Exceptions\Exception(Yii::t('app', 'Каюту {number} нельзя зарезервировать', ['number' => $number]), '400');
            }

            $orderCabin = new OrderCabin();
            $orderCabin->order_id = $order->id;
            $orderCabin->virtual_cabin_id = $virtual->id;

            if (!$orderCabin->validate()) {
                throw new ValidationException($orderCabin->errors);
            }

            $orderCabin->save();

            return $orderCabin->id;
        }

        /** @var Cabin $cabin */
        $cabin = Cabin::find()
            ->andWhere(['number' => $number])
            ->andWhere(['ship_id' => $ship->id])
            ->withoutTrashed()
            ->one();

        if ($cabin === null) {
            throw new NotFoundHttpException("Cabin (number = $number) not found");
        }

        $result = $shipService->analyseCabin($cabin);

        if (!$result->cabinSellingInTour($tour)) {
            throw new \Redbox\JsonRpc\Exceptions\Exception(Yii::t('app', 'Каюту {number} нельзя зарезервировать', ['number' => $number]), '400');
        }

        $orderCabin = new OrderCabin();
        $orderCabin->order_id = $order->id;
        $orderCabin->cabin_id = $cabin->id;

        if (!$orderCabin->validate()) {
            throw new ValidationException($orderCabin->errors);
        }

        $orderCabin->save();

        return $orderCabin->id;
    }

    public function deleteCabin(Order $order, string|int $number)
    {
        if ($order->deleted_at !== null) {
            throw new OrderServiceException('Нельзя удалить каюту из аннулированного заказа');
        }

        $paymentStatuses = [
            IConstant::PAYMENT_STATUS_PAYED,
            IConstant::PAYMENT_STATUS_OVER_PAYED,
            IConstant::PAYMENT_STATUS_PARTIALLY_PAYED,
        ];

        if (in_array($order->paymentStatus?->slug ?? '', $paymentStatuses)) {
            throw new OrderServiceException('Нельзя удалить каюту из оплаченного заказа');
        }

        $orderCabinCount = $order->getOrderCabins()->withoutTrashed()->count();

        if (($orderCabinCount - 1) <= 0) {
            throw new BadRequestHttpException('Заказ должен иметь минимум одну каюту');
        }

        $orderCabin = $order
            ->getOrderCabins()
            ->joinWith('cabin')
            ->withoutTrashed()
            ->andWhere(['cabins.number' => $number])
            ->one();

        if ($orderCabin == null) {
            throw new BadRequestHttpException("Cabin (number = $number) not found");
        }

        $orderCabin->delete();

        return $orderCabin->id;
    }

    /**
     * @param $reserveId  - идентификатор резервации
     * @param $numbers  - номера кают (не идентификаторы)
     * @param  bool  $save  - сохранить результат
     *
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function deleteCabins($reserveId, $numbers, bool $save = false): array
    {
        $order = $this->getOrderById($reserveId);

        $transaction = Yii::$app->db->beginTransaction();

        $orderCabinIds = [];

        foreach ($numbers as $number) {
            $orderCabinIds[] = $this->deleteCabin($order, $number);
        }

        if ($save) {
            $transaction->commit();
        } else {
            $transaction->rollBack();
        }

        return $orderCabinIds;
    }

    public function changeManager(
        Order $order,
        Contractor $contractor,
        bool $save = false
    ) {
        if ($contractor->contractorTypeSlug
            !== IConstant::CONTRACTOR_EMPLOYEE
        ) {
            throw new OrderServiceException('Контрагент не является сотрудником!', -400, ['currentSlug' => $contractor->contractorTypeSlug, 'expectedSlug' => IConstant::CONTRACTOR_EMPLOYEE]);
        }

        $order->manager_uid = $contractor->id;
        $order->validate(null, true, true);

        if ($save) {
            $order->save();
        }

        return true;
    }

    /**
     * @param $reserveId  - идентификатор резервации
     * @param $employContractorId  - идентификатор нового ответственного менеджера
     * @param  bool  $save  - сохранить результат
     *
     * @throws NotFoundHttpException|Throwable
     */
    public function changeManagerFromReserveContractorId(
        $reserveId,
        $employContractorId,
        bool $save = false
    ) {
        $order = $this->getOrderById($reserveId);

        $existsContractor = Contractor::find()
            ->withTrashed()
            ->byId($employContractorId)
            ->one();

        if (!$existsContractor) {
            throw new NotFoundHttpException("Contractor ($employContractorId) not found");
        }

        return $this->changeManager($order, $existsContractor, $save);
    }

    /**
     * @throws NotFoundHttpException|Throwable
     */
    public function addInsuranceToPlaceTourist(
        $orderId,
        $cabinPlaceId,
        $save = false
    ): int {
        $order = $this->getOrderById($orderId);
        $orderPlace = $this->getPlaceByOrderId($orderId, $cabinPlaceId);

        $collection = Collection::find()->one()
            ?? throw new NotFoundHttpException(Yii::t('app', 'Collection (collection = {collection}, slug = {slug}) not found', ['slug' => 'Something', 'collection' => 'Something']));

        if (!$order->_total_fact) {
            throw new Exception('Нельзя добавить страховку без фактической суммы');
        }

        $orderPlace->insurance_cid = $collection->id;

        if ($save) {
            $orderPlace->save();
        }

        return $orderPlace->id;
    }

    /**
     * @throws NotFoundHttpException|Throwable
     */
    public function addPlace(
        $reserveId,
        $number,
        $place_type,
        $save = false
    ) {
        $order = $this->getOrderById($reserveId);

        if ($order->deleted_at !== null) {
            throw new OrderServiceException('Нельзя удалить место из аннулированного заказа');
        }

        $orderCabin = $this->getCabinByOrderIdAndNumber($reserveId, $number);

        /** @var Collection $collection */
        $collection = Collection::find()
            ->collection(IConstant::COLLECTION_PLACE_TYPES)
            ->andWhere(['slug' => $place_type])
            ->one();

        if ($collection == null) {
            throw new NotFoundHttpException(Yii::t('app', 'Place type ({id}) not found', ['slug' => $place_type]));
        }

        if ($collection->slug == IConstant::PLACE_TYPE_BASE) {
            return $this->addPlaceBase($orderCabin, $save);
        } else {
            return $this->addPlaceAdvance($orderCabin, $save);
        }
    }

    /**
     * @throws NotFoundHttpException|Throwable|StaleObjectException
     */
    public function deletePlace(
        $reserveId,
        $number,
        $orderPlaceId,
        bool $save = false
    ): int {
        $order = $this->getOrderById($reserveId);

        if ($order->deleted_at !== null) {
            throw new OrderServiceException('Нельзя удалить место из аннулированного заказа');
        }

        $orderCabin = $this->getCabinByOrderIdAndNumber($reserveId, $number);

        /** @var OrderPlace $orderPlace */
        $orderPlace = $orderCabin
            ->getOrderPlaces()
            ->withoutTrashed()
            ->andWhere(['id' => $orderPlaceId])
            ->one();

        if ($orderPlace == null) {
            throw new NotFoundHttpException(Yii::t('app', 'OrderPlace ({id}) not found', ['id' => $orderPlaceId]));
        }

        if ($save) {
            $orderPlace->delete();
        }

        return $orderPlace->id;
    }

    /**
     * @throws Exception|Throwable
     */
    private function addPlaceBase(
        OrderCabin $orderCabin,
        bool $save = false
    ) {
        $shipCabinClass = $orderCabin->cabin->shipCabinClass;

        $collection = Collection::find()
            ->collection(IConstant::COLLECTION_PLACE_TYPES)
            ->slug(IConstant::PLACE_TYPE_BASE)
            ->one();

        if ($collection == null) {
            throw new NotFoundHttpException(Yii::t('app', 'Collection (collection = {collection}, slug = {slug}) not found', ['collection' => IConstant::COLLECTION_PLACE_TYPES, 'slug' => IConstant::PLACE_TYPE_BASE]));
        }

        $countBasePlace = $orderCabin
            ->getOrderPlaces()
            ->withoutTrashed()
            ->andWhere(['place_type_cid' => $collection->id])
            ->count();

        if ($shipCabinClass->max_place_base <= $countBasePlace) {
            throw new Exception('Достигнуто максимальное количество основных мест в каюте.');
        }

        $orderPlace = new OrderPlace();
        $orderPlace->order_cabin_id = $orderCabin->id;
        $orderPlace->place_type_cid = $collection->id;

        if (!$orderPlace->validate()) {
            throw new ValidationException($orderPlace->errors);
        }

        if ($save) {
            $orderPlace->save();
        }

        return $orderPlace?->id;
    }

    /**
     * @throws Exception|Throwable
     */
    private function addPlaceAdvance(
        OrderCabin $orderCabin,
        bool $save = false
    ) {
        $shipCabinClass = $orderCabin->cabin->shipCabinClass;

        $collection = Collection::find()
            ->collection(IConstant::COLLECTION_PLACE_TYPES)
            ->slug(IConstant::PLACE_TYPE_ADVANCE)
            ->one();

        if ($collection == null) {
            throw new NotFoundHttpException(Yii::t('app', 'Collection (collection = {collection}, slug = {slug}) not found', ['collection' => IConstant::COLLECTION_PLACE_TYPES, 'slug' => IConstant::PLACE_TYPE_ADVANCE]));
        }

        $countAdvancePlace = $orderCabin
            ->getOrderPlaces()
            ->withoutTrashed()
            ->andWhere(['place_type_cid' => $collection->id])
            ->count();

        if ($shipCabinClass->max_place_advance <= $countAdvancePlace) {
            throw new Exception('Достигнуто максимальное количество дополнительных мест в каюте.');
        }

        $orderPlace = new OrderPlace();
        $orderPlace->order_cabin_id = $orderCabin->id;
        $orderPlace->place_type_cid = $collection->id;

        if (!$orderPlace->validate()) {
            throw new ValidationException($orderPlace->errors);
        }

        if ($save) {
            $orderPlace->save();
        }

        return $orderPlace?->id;
    }

    /**
     * @throws NotFoundHttpException|Throwable
     */
    public function addDiscountCard(
        $orderId,
        $orderPlaceId,
        $discountCardNumber,
        $save = false
    ): int {
        $orderPlace = $this->getPlaceByOrderId($orderId, $orderPlaceId);

        $discountCard = DiscountCard::find()
            ->andWhere(['number' => $discountCardNumber])
            ->one()
            ?? throw new NotFoundHttpException(Yii::t('app', 'DiscountCard (number = {id}) not found', ['id' => $discountCardNumber]));

        $orderPlace->discount_card_id = $discountCard->id;

        if ($save) {
            $orderPlace->save();
        }

        return $orderPlace->id;
    }

    /**
     * @throws NotFoundHttpException|Throwable
     */
    public function dropDiscountCard($orderId, $cabinPlaceId, $save): int
    {
        $orderPlace = $this->getPlaceByOrderId($orderId, $cabinPlaceId);

        if ($orderPlace->discount_card_id == null) {
            throw new Exception('Order place have not discount!');
        }

        $orderPlace->discount_card_id = null;

        if ($save) {
            $orderPlace->save();
        }

        return $orderPlace->id;
    }

    /**
     * @return IGetterCabinNumber[]
     *
     * @throws NotFoundHttpException
     */
    public function getCabins(int $orderId): array
    {
        return Cabin::find()
            ->withoutTrashed()
            ->joinWith('orderCabins')
            ->joinWith('orderCabins.order')
            ->andWhere(['orders.id' => $orderId])
            ->distinct()
            ->all();
    }

    /**
     * @throws Throwable
     */
    public function updatePrices(Order $order): bool
    {
        $order->_total_plan = $order->getOrderCabins()
            ->withoutTrashed()
            ->joinWith([
                'orderPlaces' => function (OrderPlaceQuery $orderPlaceQuery) {
                    $orderPlaceQuery->withoutTrashed();
                    $orderPlaceQuery->prepare((new QueryBuilder(\Yii::$app->db)));
                },
            ])
            ->sum('order_places.total_price') ?? 0;

        $order->_total_without_commission = $order->getOrderCabins()
            ->withoutTrashed()
            ->joinWith([
                'orderPlaces' => function (OrderPlaceQuery $orderPlaceQuery) {
                    $orderPlaceQuery->withoutTrashed();
                    $orderPlaceQuery->prepare((new QueryBuilder(\Yii::$app->db)));
                },
            ])
            ->sum('order_places.total_changed_price') ?? 0;

        $order->_total_departure = $order->_total_without_commission;

        $commission_price = $order->_total_without_commission / 100
            * $order->_commission_agent;
        $order->_commission = $commission_price;
        $order->_total_fact = $order->_total_plan - $commission_price;

        return $order->save();
    }

    /**
     * @throws Throwable|NotFoundHttpException
     */
    public function updatePaymentStatus(Order $order): bool
    {
        $payedTotalPrice = $order->getOrderPayments()
            ->andWhere(['is not', 'payment_dt', null])
            ->sum('payment_amount');

        if ($payedTotalPrice == $order->_total_price) {
            $status = IConstant::PAYMENT_STATUS_PAYED;
        } else {
            if (0 < $payedTotalPrice
                && $payedTotalPrice < $order->_total_price
            ) {
                $status = IConstant::PAYMENT_STATUS_PARTIALLY_PAYED;
            } else {
                if ($order->_total_price < $payedTotalPrice) {
                    $status = IConstant::PAYMENT_STATUS_OVER_PAYED;
                } else {
                    $status = IConstant::PAYMENT_STATUS_NOT_PAYED;
                }
            }
        }

        $collection = Collection::find()
            ->slug($status)
            ->collection(IConstant::COLLECTION_PAYMENT_STATUS_TYPES)
            ->one()
            ??
            throw new NotFoundHttpException("Collection (slug = $status, collection = " . IConstant::COLLECTION_PAYMENT_STATUS_TYPES . ') not found');

        $order->payment_status_cid = $collection->id;

        return $order->save();
    }

    /**
     * @param $id
     *
     * @throws NotFoundHttpException
     */
    public function getOrderById($id): Order
    {
        /** @var Order $order */
        $order = $this->findOrderById($id);

        if ($order == null) {
            throw new OrderServiceException(Yii::t('app', 'Order ({id}) not found', ['id' => $id]), -404);
        }

        return $order;
    }

    /**
     * @param $id
     *
     * @return Order
     *
     * @throws NotFoundHttpException
     */
    private function findOrderById($id): ?Order
    {
        /** @var Order $order */
        $order = Order::find()
            ->withTrashed()
            ->byId($id)
            ->one();

        return $order;
    }

    /**
     * @throws NotFoundHttpException
     */
    private function getCabinByOrderIdAndNumber($orderId, $number): OrderCabin
    {
        $order = $this->getOrderById($orderId);

        /** @var OrderCabin $orderCabin */
        $orderCabin = $order
            ->getOrderCabins()
            ->joinWith('cabin')
            ->andWhere(['cabins.number' => $number])
            ->one();

        if ($orderCabin == null) {
            throw new NotFoundHttpException("Cabin (number = $number) not found");
        }

        return $orderCabin;
    }

    /**
     * @throws NotFoundHttpException
     */
    private function getPlaceByOrderId($orderId, $orderPlaceId): OrderPlace
    {
        $this->getOrderById($orderId);
        /** @var OrderPlace $orderPlace */
        $orderPlace = OrderPlace::find()
            ->joinWith('orderCabin')
            ->joinWith('orderCabin.order')
            ->withoutTrashed()
            ->byId($orderPlaceId)
            ->one()
            ?? throw new NotFoundHttpException(Yii::t('app', 'OrderPlace ({id}) not found', ['id' => $orderPlaceId]));

        return $orderPlace;
    }

    /**
     * @throws OrderServiceException
     */
    public function getPlaceByOrderAndPlaceId(Order $order, string|int $orderPlaceId): OrderPlace
    {
        /** @var OrderPlace|null $place */
        $place = $order->getOrderPlaces()->andWhere(['id' => $orderPlaceId])->one();

        if ($place == null) {
            throw new OrderServiceException(Yii::t('app', 'Order place ({id}) not found', ['id' => $orderPlaceId]), -404);
        }

        return $place;
    }

    public function getIdentityDocumentById($identityDocumentId): IdentityDocument
    {
        /** @var IdentityDocument|null $identityDocument */
        $identityDocument = IdentityDocument::find()->andWhere(['id' => $identityDocumentId])->one();

        if ($identityDocument == null) {
            throw new OrderServiceException(Yii::t('app', 'IdentityDocument ({id}) not found', ['id' => $identityDocumentId]), -404);
        }

        return $identityDocument;
    }

    public function orderPlaceAssignIdentityDoucment(
            int|string $orderId,
        int|string $orderPlaceId,
        int|string $identityDocumentId
    ) {
        $order = $this->getOrderById($orderId);

        if ($order->deleted_at !== null) {
            throw new OrderServiceException('Нельзя удалить место из аннулированного заказа');
        }

        $place = $this->getPlaceByOrderAndPlaceId($order, $orderPlaceId);
        $identityDocument = $this->getIdentityDocumentById($identityDocumentId);

        $place->identity_document_id = $identityDocument->id;
        $place->validate();

        $place->save();

        return true;
    }

    public function orderClearPlaceAssignIdentityDoucment(
        int|string $orderId,
        int|string $orderPlaceId
    ) {
        $order = $this->getOrderById($orderId);

        if ($order->deleted_at !== null) {
            throw new OrderServiceException('Нельзя удалить место из аннулированного заказа');
        }

        $place = $this->getPlaceByOrderAndPlaceId($order, $orderPlaceId);

        $place->identity_document_id = null;
        $place->validate();

        $place->save();

        return true;
    }

    public function orderClearTouristAddDiscountCard(
        int|string $orderId,
        int|string $touristId
    ) {
        $order = $this->getOrderById($orderId);
        $idDoc = $this->getIdentityDocumentById($touristId);

        if ($order->deleted_at !== null) {
            throw new OrderServiceException('Нельзя удалить место из аннулированного заказа');
        }

        /** @var OrderPlace[] $places */
        $places = $order->getOrderPlaces()->all();

        if (empty($places)) {
            throw new OrderServiceException('У заказа не найдены места.');
        }

        foreach ($places as $place) {
            if ($place->identity_document_id === $idDoc->id) {
                $place->discount_card_id = null;
                $place->_discount_card_percent = 0;
                $place->save(false);
            }
        }

        $order->save();
    }

    public function orderTouristAddDiscountCard(
        int|string $orderId,
        int|string $touristId,
        int|string $cardNumber
    ) {
        $order = $this->getOrderById($orderId);
        $idDoc = $this->getIdentityDocumentById($touristId);

        if ($order->deleted_at !== null) {
            throw new OrderServiceException('Нельзя удалить место из аннулированного заказа');
        }

        /** @var DiscountCard $discountCard */
        $discountCard = DiscountCard::find()->andWhere(['number' => $cardNumber])->one();
        if (!$discountCard) {
            throw new OrderServiceException('Карта не найдена!');
        }

        /** @var OrderPlace[] $places */
        $places = $order->getOrderPlaces()->all();

        if (empty($places)) {
            throw new OrderServiceException('У заказа не найдены места.');
        }

        foreach ($places as $place) {
            if ($place->identity_document_id === $idDoc->id) {
                if ($place->discount_card_id !== null) {
                    throw new OrderServiceException('У туриста ID:{touristId} уже указана дисконтная карта', -400, ['touristId' => $place->identity_document_id]);
                }
                $place->discount_card_id = $discountCard->id;
                $place->_discount_card_percent = $discountCard->value;
                $place->save(false);
            }
        }

        $order->save();
    }

    public function orderTouristAddInsurance(
        int|string $orderId,
        array $touristIds,
        int|string $insuranceTypeSlug
    ) {
        $insuranceType = Collection::find()->slug($insuranceTypeSlug)->collection(IConstant::COLLECTION_INSURANCE_TYPES)->one();

        $order = $this->getOrderById($orderId);

        if (empty($touristIds)) {
            throw new OrderServiceException('Список туристов не должен быть пуст!');
        }
        if ($order->deleted_at !== null) {
            throw new OrderServiceException('Нельзя удалить место из аннулированного заказа');
        }

        /** @var OrderPlace[] $places */
        $places = $order->getOrderPlaces()->all();

        if (empty($places)) {
            throw new OrderServiceException('У заказа не найдены места.');
        }

        foreach ($places as $place) {
            if (in_array($place->identity_document_id, $touristIds)) {
                if ($place->insurance_cid !== null) {
                    throw new OrderServiceException('У туриста ID:{touristId} уже есть страховка', -400, ['touristId' => $place->identity_document_id]);
                }
                $place->insurance_cid = $insuranceType->id;
                $place->save(false);
                //todo: интеграция с Альфа
            }
        }

        $order->save();

        return true;
    }

    public function checkCabinStatus($ship_id, $numbers): void
    {
        /** @var ShipService $shipService */
        $shipService = Yii::$container->get(ShipService::class);

        $result = $shipService->analyseIntersections($ship_id, $numbers);

        foreach ($numbers as $number) {
            $cabinStatus = $result['data'][$number];

            if ($cabinStatus['reserve']) {
                throw new Exception("Каюта {$number} уже зарезервирована");
            }

            if ($cabinStatus['lock']) {
                throw new Exception("Каюта {$number} заблокирована");
            }

            if ($cabinStatus['split']) {
                throw new Exception("Каюта {$number} разделена");
            }
        }
    }

    public function getReservesByTourIds($tourIds): array
    {
        return Order::find()
            ->withoutTrashed()
            ->andWhere(['in', 'tour_id', $tourIds])
            ->all();
    }

    public function getOrderTouristCount(Tour $tour): int
    {
        $result = 0;
        foreach ($tour->orders as $order) {
            $result += $order->touristCount;
        }

        return $result;
    }

    public function getOrderCabinCount(Tour $tour): int
    {
        $result = 0;
        foreach ($tour->orders as $order) {
            if ($order->orderType->eq(IConstant::ORDER_TYPE_GENERAL)) {
                $result += $order->cabinCount;
            }
        }

        return $result;
    }

    public function getReserveAmount(Tour $tour): int|float
    {
        $result = 0;
        foreach ($tour->orders as $order) {
            $result += $order->_total_without_commission;
        }

        return $result;
    }

    public function getPaymentAmount(Tour $tour): int|float
    {
        $result = 0;
        foreach ($tour->orders as $order) {
            $result += $order->_total_payment;
        }

        return $result;
    }

    public function getCommissionAmount(Tour $tour): int|float
    {
        $result = 0;
        foreach ($tour->orders as $order) {
            $result += $order->_commission;
        }

        return $result;
    }

    public function getDiscountAmount(Tour $tour): int|float
    {
        $result = 0;
        foreach ($tour->orders as $order) {
            $result += $order->_total_discount_amount;
        }

        return $result;
    }

    /**
     * @param  bool  $resetCache  - сбросить кеш значений
     *
     * @return int|string
     */
    public function discountOrderPlace(
        OrderPlace $orderPlace,
        bool $resetCache = false
    ) {
        if ($orderPlace->total_changed_price !== null) {
            return 0;
        }

        if ($resetCache) {
            $orderPlace->_discount_category_constant_amount = null;
            $orderPlace->_discount_category_default_amount = null;
            $orderPlace->_discount_category_early_amount = null;
            $orderPlace->_discount_category_online_amount = null;
            $orderPlace->_discount_card_percent = null;
        }

        $discountPercent = 0;
        if ($orderPlace->_discount_category_constant_amount === null
            && $d1 = $orderPlace->discountCategoryConstant
        ) {
            $orderPlace->_discount_category_constant_amount = $d1->value;
        }

        if ($orderPlace->_discount_category_default_amount === null
            && $d2 = $orderPlace->discountCategoryDefault
        ) {
            $orderPlace->_discount_category_default_amount = $d2->value;
        }

        if ($orderPlace->_discount_category_early_amount === null
            && $d3 = $orderPlace->discountCategoryEarly
        ) {
            $orderPlace->_discount_category_early_amount = $d3->value;
        }

        if ($orderPlace->_discount_category_online_amount === null
            && $d4 = $orderPlace->discountCategoryOnline
        ) {
            $orderPlace->_discount_category_online_amount = $d4->value;
        }

        if ($orderPlace->_discount_card_percent === null
            && $d5 = $orderPlace->discountCard
        ) {
            $orderPlace->_discount_card_percent = $d5->value;
        }

        $discountPercent += $orderPlace->_discount_category_constant_amount ??
            0;
        $discountPercent += $orderPlace->_discount_category_default_amount ?? 0;
        $discountPercent += $orderPlace->_discount_category_early_amount ?? 0;
        $discountPercent += $orderPlace->_discount_category_online_amount ?? 0;
        $discountPercent += $orderPlace->_discount_card_percent ?? 0;

        return $discountPercent;
    }

    /**
     * @deprecated Перенесено в хранимую процедуру
     *
     * @param  bool  $resetCache  - сбросить кеш значений
     *
     * @return void
     */
    public function recalculateOrderPlace(
        OrderPlace $orderPlace,
        bool $resetCache = false
    ) {
//        if ($resetCache) {
//            $orderPlace->price_plan = null;
//        }
//
//        $price = $orderPlace->price_plan;
//        if ($orderPlace->total_changed_price !== null) {
//            $price = $orderPlace->price_plan = $orderPlace->total_changed_price;
//        }
////        $cabinPrice = $orderPlace->tourCabinPrice;
//        dd($orderPlace->tourCabinPrice);
//
//        if ($price === null) {
//            try {
//                $cabinPrice = $orderPlace->tourCabinPrice;
//                $basicPrice = $cabinPrice->price;
//                $advancePrice = $cabinPrice->additional_price;
//                $price = $orderPlace->price_plan = $advancePrice;
//                if ($orderPlace->cabin_place_id === null) {
//                    $price = $orderPlace->price_plan = $basicPrice;
//                }
//            } catch (Exception $e) {
//                dd(ArrayHelper::toArray($cabinPrice),
//                    ArrayHelper::toArray($orderPlace));
//            }
//        }
//
////        $discountPercent = $this->discountOrderPlace($orderPlace, $resetCache);
//
//        $orderPlace->price_plan = $price;
//        $orderPlace->total_price = $price - ($price * ($discountPercent ?? 0 / 100));
    }

    public function updateTotalPriceAmount(Order $order)
    {
        $this->recalculateOrderTotals($order);
        $order->updateAttributes([
            '_total_plan' => $order->_total_plan,
            '_total_departure' => $order->_total_departure,
            '_total_price' => $order->_total_price,
            '_total_insurance' => $order->_total_insurance,
            '_commission_agent' => $order->_commission_agent,
            '_commission' => $order->_commission,
            '_total_without_commission' => $order->_total_without_commission,
            '_total_discount_percent' => $order->_total_discount_percent,
            '_total_discount_amount' => $order->_total_discount_amount,
            '_total_payment' => $order->_total_payment,
            '_total_fact' => $order->_total_fact,
        ]);
    }

    public function recalculateOrderTotals(Order $order)
    {
        $totalDiscountAmount = 0;
        $totalDeparture = 0;
        $totalPrice = 0;
        $totalPlan = 0;
        $totalInsurance = 0;
        $totalPayment = 0;

        foreach ($order->orderCabins as $orderCabin) {
            /** @var OrderPlace[] $orderPlaces */
            $orderPlaces = OrderPlace::find()
                ->andWhere(['order_cabin_id' => $orderCabin->id])->all();
            foreach ($orderPlaces as $place) {
//                $totalDiscountAmount += $place->price_plan - $place->total_price;
//                $totalPrice += $place->total_price;
//                $totalDeparture += $place->total_price;
//                $totalPlan += $place->price_plan;
//                $totalInsurance += 0; // todo: Добавить расчёт страховки
            }
        }
//        foreach ($order->orderPayments as $payment) {
//            if ($payment->statusSlug
//                === IConstant::ORDER_PAYMENT_STATUS_DEPOSIT
//            ) {
//                $totalPayment += $payment->payment_amount;
//            }
//        }

        /** @var Tour $tour */
        $tour = Tour::find()
            ->byId($order->tour_id)
            ->one();

        $order->_total_plan = $totalPlan;
        $order->_total_departure = $totalDeparture;
        $order->_total_price = $totalPrice;
        $order->_total_insurance = $totalInsurance;

        if (!$tour->commission_disable) {
            $order->_commission_agent = $order->contractor->commission_self ?? 0;
            $order->_commission = round(($order->_commission_agent / 100)
                * $totalPrice, 2);
            $order->_total_without_commission = round($totalPrice
                - $order->_commission, 2);
        }

        if ($totalPrice > 0 || $totalPrice < 0) {
            $order->_total_discount_percent = round(
                $totalDiscountAmount * 100 / $totalPrice,
                2
            );
        }
        $order->_total_discount_amount = $totalDiscountAmount;
        $order->_total_payment = $totalPayment;
        $order->_total_fact = $totalPayment;
    }

    public function getTotalPayment(Order $order)
    {
        /** @var OrderPayment[] $payments */
        $payments = OrderPayment::find()->andWhere(['order_id' => $order->id])
            ->all();
        $amount = 0;
        foreach ($payments as $payment) {
            if ($payment->statusSlug
                === IConstant::ORDER_PAYMENT_STATUS_DEPOSIT
            ) {
                $amount += $payment->payment_amount;
            }
        }

        return $amount;
    }

    /**
     * @deprecated перенесено в orders_update_cache
     *
     * @return array|\common\database\Collection|null
     */
    public function getPaymentStatus(Order $order)
    {
        $payment = $this->getTotalPayment($order) ?? 0;
        $price = $order->_total_price ?? 0;

        if ($payment == $price) {
            return Collection::find()
                ->collection(IConstant::COLLECTION_PAYMENT_STATUS_TYPES)
                ->slug(IConstant::PAYMENT_STATUS_PAYED)->one();
        }

        if ($payment > $price) {
            return Collection::find()
                ->collection(IConstant::COLLECTION_PAYMENT_STATUS_TYPES)
                ->slug(IConstant::PAYMENT_STATUS_OVER_PAYED)->one();
        }

        if ($payment < $price && $payment > 0) {
            return Collection::find()
                ->collection(IConstant::COLLECTION_PAYMENT_STATUS_TYPES)
                ->slug(IConstant::PAYMENT_STATUS_PARTIALLY_PAYED)->one();
        }

        return Collection::find()
            ->collection(IConstant::COLLECTION_PAYMENT_STATUS_TYPES)
            ->slug(IConstant::PAYMENT_STATUS_NOT_PAYED)->one();
    }

    /**
     * @throws NotFoundHttpException|ValidationException
     */
    public function addExcursion(int|string $orderId, int|string $excursionId)
    {
        $order = $this->getOrderById($orderId);

        if ($order->deleted_at !== null) {
            throw new OrderServiceException('Нельзя удалить место из аннулированного заказа');
        }

        $excursion = Excursion::findOne($excursionId);
        if (!$excursion) {
            throw new NotFoundHttpException("Excursion ($excursionId) not found!");
        }

        $isAvailableExcursionQ = $excursion->getTourExcursions()
            ->andWhere(['tour_excursions.has_sell' => true])
            ->andWhere(['tour_excursions.tour_id' => $order->tour_id]);

        $isAvailableExcursion = $isAvailableExcursionQ->exists();

        if (!$isAvailableExcursion) {
            throw new NotFoundHttpException("Excursion ($excursionId) not available!");
        }

        $orderExcursionExist = OrderExcursion::find()->andWhere([
            'order_id' => $order->id,
            'excursion_id' => $excursion->id,
        ])->exists();

        if ($orderExcursionExist) {
            throw new OrderServiceException('Попытка повторного добавления экскурсии');
        }

        $orderExcursion = new OrderExcursion();
        $orderExcursion->order_id = $order->id;
        $orderExcursion->excursion_id = $excursion->id;

        if (!$orderExcursion->validate()) {
            throw new ValidationException($orderExcursion->errors);
        }

        $orderExcursion->save();

        return $orderExcursion->id;
    }

    /**
     * @throws NotFoundHttpException|StaleObjectException|Throwable
     */
    public function deleteExcursion(int $orderId, int $excursionId): bool
    {
        $order = $this->getOrderById($orderId);

        if ($order->deleted_at !== null) {
            throw new OrderServiceException('Нельзя удалить место из аннулированного заказа');
        }

        $orderExcursion = $this->getOrderById($orderId)
            ->getOrderExcursions()
            ->andWhere(['excursion_id' => $excursionId])
            ->one();

        if (!$orderExcursion) {
            throw new NotFoundHttpException("Order ($orderId) has not excursion ($excursionId)");
        }

        return $orderExcursion->delete();
    }

    /**
     * @throws NotFoundHttpException|Throwable
     */
    public function updateReserveEndDate($orderId, $newReserveEndDate): bool
    {
        $order = $this->getOrderById($orderId);
        $order->reserve_end_date = Carbon::parse($newReserveEndDate);
        return $order->save();
    }
}
