<?php

namespace tests\unit;

use common\components\services\NavigationService;
use common\components\services\OrderService;
use common\components\services\ShipService;
use common\IConstant;
use common\models\Account;
use common\models\Ship;
use common\models\Tour;
use tests\fixtures\AccountFixture;
use tests\fixtures\OrderFixture;
use tests\fixtures\ShipFixture;
use tests\fixtures\TourFixture;
use yii\helpers\ArrayHelper;

class ShipServiceTest extends \Codeception\Test\Unit
{
    public function _fixtures()
    {
        return [
            'accounts' => [
                'class' => AccountFixture::class,
            ],
            'ships' => [
                'class' => ShipFixture::class,
            ],
            'tours' => [
                'class' => TourFixture::class,
            ],
            'orders' => [
                'class' => OrderFixture::class,
            ],
        ];
    }

    public function testFindTour()
    {
//        $this->expectException(\Exception::class);

        $service = \Yii::$container->get(ShipService::class);
        $tour = $service->getTour(1);

        $this->assertNotNull($tour);
        $this->assertEquals(1, $tour->id);
    }

    public function testFindTours()
    {
        $service = \Yii::$container->get(ShipService::class);
        $tours = $service->findTours(2);
        $this->assertCount(1, $tours);
    }

    public function testFindPlaces()
    {
//        $service = \Yii::$container->get(ShipService::class);
//        $tours = $service->findPlaces(2);
    }

    public function testFindCabin()
    {
        $service = \Yii::$container->get(ShipService::class);
        $cabin = $service->findCabin('101', '1');

        $this->assertNotNull($cabin);
        $this->assertEquals(1, $cabin->id);
    }

    public function testFindShipById()
    {
        $service = \Yii::$container->get(ShipService::class);
        $ship = $service->findShipById(1);

        $this->assertNotNull($ship);
        $this->assertEquals('Корабль 1', $ship->title);
    }

    public function testAnalyseIntersections()
    {
        /** @var Tour $tour */
        $tour = Tour::find()->byId(4)->one();
        /** @var Tour $tour1 */
        $tour1 = Tour::find()->byId(5)->one();

        \Yii::$app->user->login(Account::find()->one());
        $orderService = \Yii::$container->get(OrderService::class);
        $service = \Yii::$container->get(ShipService::class);
        $orderService->addTempReserve(4, ['102'], 'Коммент', 1, true);
        $service->lockCabins(1, 5, 1, ['102'], true);
//        $result = $service->getCabinLockData(1, '101', null);
//        dd($result);
//        $service->unlockCabins(1, null,1, ["101"], true);
//        $service->splitCabin(1, 1, "101", [1,2,3], IConstant::GENDER_MEN,true);
        $ship = $service->analyseCabins(1, ['102']);

        dd(ArrayHelper::toArray($ship));
    }

    public function testGetShipById()
    {
    }

    public function testUnlockCabins()
    {
    }

    public function testVirtualName()
    {
    }

    public function testSplitCabin()
    {
        /** @var Account $account */
        $account = Account::find()->one();

        /** @var Tour $tour */
        $tour = Tour::find()->byId(4)->one();

        /** @var Ship $ship */
        $ship = $tour->ship;

        $service = \Yii::$container->get(ShipService::class);
        $serviceN = \Yii::$container->get(NavigationService::class);

        \Yii::$app->user->login($account);

//        $result = $service->lockCabins(1,1,"Новый", ["101"], true);
//        dd($result);
        $service->splitCabin(
            $ship->id,
            $tour->id,
            '101',
            ['а', 'б'],
            'man',
            true
        );

//
//        $service->splitCabin(
//            $ship->id,
//            $tour->id,
//            "101",
//            ['б', 'в'],
//            "man",
//            true
//        );
    }

    public function testGetCabinsStatuses()
    {
    }

    public function testFindCabinExcludeGroup()
    {
    }

    public function testLockCabins()
    {
    }

    public function testDeleteCabin()
    {
    }

    public function testConstruct()
    {
    }

    public function testFindCabinExclude()
    {
    }

    public function testFindVirtualCabin()
    {
    }

    public function testFindShip()
    {
    }

    public function testFindVirtualCabins()
    {
    }

    public function testAddCabin()
    {
    }

    public function testGetCabinByNumber()
    {
    }
}
