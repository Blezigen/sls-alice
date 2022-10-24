<?php

namespace tests\unit;

use common\components\services\OrderService;
use common\DiscountService;
use common\IConstant;
use common\models\Collection;
use common\models\Discount;
use common\models\Order;
use common\models\OrderPlace;
use common\models\Tour;
use tests\fixtures\CollectionFixture;
use tests\fixtures\DiscountCardFixture;
use tests\fixtures\DiscountFixture;
use tests\fixtures\OrderFixture;
use tests\fixtures\TourFixture;
use yii\helpers\ArrayHelper;

class DiscountServiceTest extends \Codeception\Test\Unit
{
    public DiscountService $discountService;
    public OrderService $orderService;

    public function _fixtures(): array
    {
        return [
            'collections' => [
                'class' => CollectionFixture::class,
            ],
            'tour' => [
                'class' => TourFixture::class,
            ],
            'order' => [
                'class' => OrderFixture::class,
            ],
            'discounts' => [
                'class' => DiscountFixture::class,
            ],
            'discount_cards' => [
                'class' => DiscountCardFixture::class,
            ],
        ];
    }

    protected function _setUp(): void
    {
        $this->discountService = \Yii::$container->get(DiscountService::class);
        $this->orderService = \Yii::$container->get(OrderService::class);
    }

    public function testLoad()
    {
        $data = $this->discountService->load();
        $this->assertTrue(count($this->discountService->discounts) > 0);
    }


    /**
     *
     * @return void
     */
    public function testGetDiscounts()
    {
        $this->discountService->load();
        $data = $this->discountService->getDiscounts(IConstant::DISCOUNT_TYPE_BASE);

        $assertData = ArrayHelper::index($data, "id");

        // Check base type
        $this->assertArrayHasKey(1, $assertData);
        $this->assertArrayHasKey(4, $assertData);
        $this->assertArrayHasKey(8, $assertData);
        $this->assertArrayHasKey(16, $assertData);
        // Check client type
        $this->assertArrayNotHasKey(2, $assertData, "Метод getDiscount не верно возвращает значение");
    }

    public function canUseDiscountProvider()
    {
        return [
            [null, 1, 1, true], // Действует на любые 3 заказа.
            [null, 1, 4, true], // Система смотрит на переменные возраста для пенсионера
            [null, 1, 8, false], // Система смотрит на переменные возраста для ребёнка
            [null, 1, 16, true], // Система смотрит на документ, который подтверждает день рождения.
            [null, 1, 18, true], // Смотрит на параметры скидки, отображается в случае если соблюдены правила.
        ];
    }

    /**
     * @dataProvider canUseDiscountProvider
     */
    public function testCanUseDiscounts($type, $orderPlace, $discountId, $result)
    {
        $this->discountService->load();

        /** @var OrderPlace $orderPlace */
        $orderPlace = OrderPlace::find()
            ->byId($orderPlace)
            ->one();


        /** @var Discount $discount */
        $discount = Discount::find()
            ->byId($discountId)
            ->one();

        echo "Discount cause type: ".$discount->discountCauseType->slug.PHP_EOL;
        echo "Dob: ".$orderPlace->identityDocument->birth_date.PHP_EOL;
        echo "Age: ".$orderPlace->identityDocument->getAge().PHP_EOL;

        $data = $this->discountService->canUseDiscount($discount, $orderPlace);

        $this->assertEquals($data, $result, $result ? "Скидка должна быть применена" : "Скидка не может быть применена");
    }

}