<?php

namespace integration\models\mintrans;

use common\IConstant;
use common\models\IdentityDocument;
use common\models\Order;
use common\models\ShipTeam;
// use yii\web\HttpException;
use integration\services\MintransService;
use yii\base\Model;
use yii\console\Exception;

class MintransShip extends Model
{
    public $surname;
    public $name;
    public $patronymic;
    public $birthday;
    public $docType;
    public $docNumber;
    public $documentAdditionalInfo;
    public $departPlace;
    public $arrivePlace;
    public $routeType;
    public $departDate;
    public $departDateFact;
    public $citizenship;
    public $gender;
    public $recType;
    public $rank;
    public $operationType;
    public $operatorId;
    public $route;
    public $places;
    public $reservedSeatsCount;
    public $buyDate;
    public $termNumOrSurname;
    public $arriveDate;
    public $arriveDateFact;
    public $deckNumber;
    public $roomNumber;
    public $shipClass;
    public $shipNumber;
    public $shipName;
    public $flagState;
    public $registerTimeIS;
    public $operatorVersion = 20;

    public const SCENARIO_TOURIST = 'tourist';
    public const SCENARIO_CREW = 'crew';

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['surname', 'name', 'patronymic', 'birthday', 'docType', 'docNumber', 'departPlace', 'arrivePlace', 'routeType', 'departDate', 'citizenship', 'gender', 'recType', 'operationType', 'operatorId', 'route', 'arriveDate', 'shipClass', 'shipNumber', 'shipName', 'flagState', 'registerTimeIS'], 'required'],
            [['birthday', 'departDate', 'departDateFact', 'buyDate', 'arriveDate', 'arriveDateFact', 'registerTimeIS'], 'safe'],
            [['docType', 'routeType', 'recType', 'operationType', 'operatorId', 'shipClass', 'operatorVersion'], 'integer'],
            [['surname'], 'string', 'max' => 40],
            [['name', 'patronymic', 'citizenship', 'flagState'], 'string', 'max' => 30],
            [['docNumber', 'departPlace', 'arrivePlace', 'rank', 'termNumOrSurname', 'shipName'], 'string', 'max' => 20],
            [['documentAdditionalInfo', 'route'], 'string', 'max' => 200],
            [['gender', 'deckNumber'], 'string', 'max' => 1],
            [['shipNumber'], 'string', 'max' => 8],
            [['places'], 'string', 'max' => 4],
            [['reservedSeatsCount'], 'string', 'max' => 2],
            [['roomNumber'], 'string', 'max' => 6],

            ['docType', 'in', 'range' => array_keys(self::getDocTypes())],
            ['routeType', 'in', 'range' => array_keys(self::getRouteTypes())],
            ['operationType', 'in', 'range' => array_keys(self::getOperationTypes())],
            ['gender', 'in', 'range' => array_keys(self::getGenders())],
            ['recType', 'in', 'range' => array_keys(self::getRecTypes())],
            ['shipClass', 'in', 'range' => array_keys(self::getShipClasses())],

            [['buyDate'], 'required', 'on' => self::SCENARIO_TOURIST],
            [['rank'], 'required', 'on' => self::SCENARIO_CREW],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'surname' => 'Surname',
            'name' => 'Name',
            'patronymic' => 'Patronymic',
            'birthday' => 'Birthday',
            'docType' => 'Doc Type',
            'docNumber' => 'Doc Number',
            'documentAdditionalInfo' => 'Document Additional Info',
            'departPlace' => 'Depart Place',
            'arrivePlace' => 'Arrive Place',
            'routeType' => 'Route Type',
            'departDate' => 'Depart Date',
            'departDateFact' => 'Depart Date Fact',
            'citizenship' => 'Citizenship',
            'gender' => 'Gender',
            'recType' => 'Rec Type',
            'rank' => 'Rank',
            'operationType' => 'Operation Type',
            'operatorId' => 'Operator ID',
            'route' => 'Route',
            'places' => 'Places',
            'reservedSeatsCount' => 'Reserved Seats Count',
            'buyDate' => 'Buy Date',
            'termNumOrSurname' => 'Term Num Or Surname',
            'arriveDate' => 'Arrive Date',
            'arriveDateFact' => 'Arrive Date Fact',
            'deckNumber' => 'Deck Number',
            'roomNumber' => 'Room Number',
            'shipClass' => 'Ship Class',
            'shipNumber' => 'Ship Number',
            'shipName' => 'Ship Name',
            'flagState' => 'Flag State',
            'registerTimeIS' => 'Register Time Is',
            'operatorVersion' => 'Operator Version',
        ];
    }

    public function getDocTypes()
    {
        return [
            0 => 'Паспорт гражданина Российской Федерации',
            1 => 'Удостоверение личности моряка (паспорт моряка)',
            2 => 'Общегражданский заграничный паспорт гражданина Российской Федерации',
            3 => 'Паспорт иностранного гражданина',
            4 => 'Свидетельство о рождении',
            5 => 'Удостоверение личности военнослужащего',
            6 => 'Удостоверение личности лица без гражданства',
            7 => 'Временное удостоверение личности, выдаваемое органами внутренних дел',
            8 => 'Военный билет военнослужащего срочной службы',
            9 => 'Вид на жительство иностранного гражданина или лица без гражданства',
            10 => 'Справка об освобождении из мест лишения свободы',
            11 => 'Паспорт гражданина СССР',
            12 => 'Паспорт дипломатический',
            13 => 'Паспорт служебный (кроме паспорта моряка и дипломатического)',
            14 => 'Свидетельство о возвращении из стран СНГ',
            15 => 'Справка об утере паспорта',
            18 => 'Свидетельство о предоставлении временного убежища',
            99 => 'Другие документы, установленные федеральным законодательством или
            признаваемые в соответствии с международными договорами РФ в качестве
            документов, удостоверяющих личность пассажира',
        ];
    }

    public function getRouteTypes()
    {
        return [
            0 => 'Беспересадочный',
            1 => 'Транзитный',
        ];
    }

    public function getOperationTypes()
    {
        return [
            0 => 'Бронирование',
            1 => 'Покупка',
            2 => 'Возврат',
            3 => 'Переоформление',
            4 => 'Регистрация',
            5 => 'Спецконтроль',
            6 => 'Посадка',
            7 => 'Прибытие (высадка)',
            8 => 'Гашение',
            9 => 'Отказ от заказа',
            10 => 'Бронирование через сеть Интернет',
            11 => 'On-line регистрация',
            12 => 'Предварительное бронирование',
            13 => 'Отказ от предварительного бронирования',
            14 => 'Отправление',
            15 => 'Отмена отправления',
            16 => 'Снятие с регистрации',
            17 => 'Корректировка данных',
            50 => 'Включение в состав экипажа',
            51 => 'Исключение из состава экипажа',
        ];
    }

    public function getGenders()
    {
        return [
            'M' => 'Мужской',
            'F' => 'Женский',
        ];
    }

    public function getRecTypes()
    {
        return [
            0 => 'Персональные данные персонала транспортного средства',
            1 => 'Персональные данные пассажира',
        ];
    }

    public function getShipClasses()
    {
        return [
            // 1 => '2'
            0 => 'Морские (дальнего, неограниченного, прибрежного)',
            1 => 'Рейдовые',
            2 => 'Внутреннего плавания (речные, озерные)',
            3 => 'Смешанного плавания (река - море)',
        ];
    }

    public static function formatTour($data)
    {
        date_default_timezone_set('Europe/Moscow');

        // $orders = $data->orders;
        $result = [];

        $orders = Order::find()
            ->withoutTrashed()
            ->joinwith('paymentStatus as ps')
            ->andWhere([
                'tour_id' => $data->id,
                'ps.slug' => IConstant::PAYMENT_STATUS_PAYED,
            ])
            ->all();

        /*
            Перебор заказов и форматирование
        */
        foreach ($orders as $order) {
            /* start test */
            $buyDate = date('c');
            /* end test */

            foreach ($order->orderCabins as $orderCabin) {
                foreach ($orderCabin->orderPlaces as $p) {
                    $document = isset($p->identityDocument) ? $p->identityDocument : IdentityDocument::find()
                        ->byId($p->identity_document_id)
                        ->one();

                    if (!$document) {
                        self::addLog("Не найден IdentityDocument для OrderPlace id {$p->id}");
                        continue;
                        // throw new Exception("Не найден IdentityDocument для OrderPlace id {$p->id}");
                    }

                    if ($formatedItem = self::formatItem($document, $data, $buyDate)) {
                        $result[] = $formatedItem;
                    }
                }
            }
        }

        /*
            Перебор команды и форматирование
        */
        $team = ShipTeam::find()
            // ->byVersion()
            ->withoutTrashed()
            ->andWhere(['ship_id' => $data->ship_id])
            ->all();

        foreach ($team as $t) {
            if (!$t->ship_position) {
                self::addLog("Не указан ship_position для ShipTeam id {$t->id}");
                continue;
                // throw new Exception("Не указан ship_position для ShipTeam id {$t->id}");
            }

            $document = isset($t->identityDocument) ? $t->identityDocument : IdentityDocument::find()
                ->byId($t->identity_document_id)
                ->one();

            if (!$document) {
                self::addLog("Не найден IdentityDocument для ShipTeam id {$t->id}");
                continue;
                // throw new Exception("Не найден IdentityDocument для ShipTeam id {$t->id}");
            }

            $formtedItem = self::formatItem($document, $data, null, $t->ship_position);

            if ($formtedItem) {
                $result[] = $formtedItem;
            }
        }

        return $result;
    }

    public static function formatItem($document, $tour, $buyDate = null, $rank = null)
    {
        $navigation = $tour->navigations;
        $ship = $tour->ship;
        $isTourist = $rank ? false : true;

        if (!$navigation) {
            self::addLog("У тура {$tour->id} не указана навигация");

            return false;
            // throw new Exception("У тура {$tour->id} не указана навигация");
        }

        $routes = [];
        foreach ($navigation as $navigate) {
            $routes[] = $navigate->city->title;
        }

        $operatorId = \Yii::$app->params['mintrans']['operatorId']; // Код перевозчика в ЕГИС ОТБ
        $shipClass = 2; // Внутреннего плавания (речные, озерные)

        $registerTimeIS = date('c');
        $route = implode(' - ', $routes);

        $departPlace = $routes[0];
        $lastRouteIndex = count($routes) - 1;
        $arrivePlace = $routes[$lastRouteIndex];

        $data = [
            'surname' => $document->last_name,
            'name' => $document->first_name,
            'patronymic' => $document->third_name || 'NA',
            'birthday' => $document->birth_date,
            // 'birthPlace' => '',
            'docType' => $isTourist ? 0 : 1, // Тип документа (паспорт РФ или паспорт моряка)
            'docNumber' => "{$document->serial}{$document->number}",
            'documentAdditionalInfo' => '',
            'departPlace' => $departPlace,
            'arrivePlace' => $arrivePlace,
            'routeType' => 0, // Вид маршрута следования
            'departDate' => $tour->departure_dt,
            'citizenship' => 'RUS',
            'gender' => $document->gender && $document->gender->slug == 'woman' ? 'F' : 'M',
            'recType' => $isTourist ? 1 : 0,
            'rank' => $isTourist ? '' : $rank,
            'operationType' => $isTourist ? 1 : 50,
            'operatorId' => $operatorId,
            'route' => $route,
            'reservedSeatsCount' => $isTourist ? 1 : '',
            'buyDate' => $buyDate,
            'termNumOrSurname' => $isTourist ? 'NA' : '',
            'arriveDate' => $tour->arrival_dt,
            // 'arriveDateFact' => 'Arrive Date Fact',
            // 'deckNumber' => '',
            // 'roomNumber' => '',
            'shipClass' => $shipClass,
            'shipNumber' => $ship->imo_number || $ship->rus_number,
            'shipName' => $ship->title,
            'flagState' => 'RU',
            'registerTimeIS' => $registerTimeIS,
            'operatorVersion' => 20, // Номер версии (формата обменного файла). Обязательное поле. Указывается значение «20».
        ];

        /* start Тестовые данные */
        // $faker = new \integration\helpers\FakeData();
        // $data = $faker->getMintransUser();
        /* end Тестовые данные */

        $dateFormat = "Y-m-d\TH:mP";
        $dateAttr = ['departDate', 'departDateFact', 'buyDate', 'arriveDate', 'registerTimeIS'];

        foreach ($data as $key => $value) {
            if (in_array($key, $dateAttr) && $value) {
                $value = date($dateFormat, strtotime($value));
            }

            $data[$key] = $value;
        }

        return $data;
    }

    private static function addLog($message)
    {
        MintransService::addLog($message);
    }
}
