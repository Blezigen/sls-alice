<?php

namespace api\modules\users\controllers;

use filsh\yii2\oauth2server\filters\auth\CompositeAuth;
use filsh\yii2\oauth2server\filters\ErrorToExceptionFilter;
use GuzzleHttp\Client;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\helpers\ArrayHelper;
use yii\web\User;

class DeviceController extends \yii\web\Controller
{
    /**
     * Устройство, которое имеет управляемые светящиеся элементы.
     * Example: Лампочка, светильник, ночник, люстра.
     */
    public const DEVICE_TYPE_LIGHT = 'devices.types.light';
    /**
     * Розетка.
     * Example: Умная розетка.
     */
    public const DEVICE_TYPE_SOCKET = 'devices.types.socket';
    /**
     * Выключатель.
     * Example: Настенный выключатель света, тумблер, автомат в электрическом щитке, умное реле, умная кнопка.
     */
    public const DEVICE_TYPE_SWITCH = 'devices.types.switch';
    /**
     * Устройство с возможностью регулирования температуры.
     * Example: Водонагреватель, теплый пол, обогреватель, электровентилятор. Для кондиционера рекомендуется использовать отдельный тип devices.types.thermostat.ac .
     */
    public const DEVICE_TYPE_THERMOSTAT = 'devices.types.thermostat';
    /**
     * Устройство, управляющее микроклиматом в помещении, с возможностью регулирования температуры и режима работы.
     * Example: Кондиционер.
     */
    public const DEVICE_TYPE_THERMOSTAT_AC = 'devices.types.thermostat.ac';
    /**
     * Аудио, видео, мультимедиа техника. Устройства, которые умеют воспроизводить звук и видео.
     * Example: DVD-плеер и другие медиаустройства. Для телевизора рекомендуется использовать отдельный тип devices.types.media_device.tv, для умной ТВ-приставки — devices.types.media_device.tv_box, для ресивера — devices.types.media_device.receiver .
     */
    public const DEVICE_TYPE_MEDIA_DEVICE = 'devices.types.media_device';
    /**
     * Устройство для просмотра видеоконтента. На устройстве можно изменять громкость и переключать каналы.
     * Example: Умный телевизор, ИК-пульт от телевизора, медиаприставка, ресивер.
     */
    public const DEVICE_TYPE_MEDIA_DEVICE_TV = 'devices.types.media_device.tv';
    /**
     * Устройство, подключаемое к телевизору или дисплею, для просмотра видеоконтента. На устройстве можно управлять громкостью воспроизведения и переключать каналы.
     * Example: ИК-пульт от ТВ-приставки, умная ТВ-приставка.
     */
    public const DEVICE_TYPE_MEDIA_DEVICE_TV_BOX = 'devices.types.media_device.tv_box';
    /**
     * Устройство, подключаемое к телевизору или дисплею, для просмотра видеоконтента. На устройстве можно изменять громкость, переключать каналы и источники аудио-/видеосигнала.
     * Example: ИК-пульт от ресивера, AV-ресивер, спутниковый ресивер.
     */
    public const DEVICE_TYPE_MEDIA_DEVICE_RECEIVER = 'devices.types.media_device.receiver';
    /**
     * Различная умная кухонная техника.
     * Example: Холодильник, духовой шкаф, кофеварка, мультиварка. Для чайника рекомендуется использовать отдельный тип devices.types.cooking.kettle, для кофеварки — devices.types.cooking.coffee_maker .
     */
    public const DEVICE_TYPE_COOKING = 'devices.types.cooking';
    /**
     * Устройство, которое умеет делать кофе.
     * Example: Кофеварка, кофемашина.
     */
    public const DEVICE_TYPE_COOKING_COFFEE_MAKER = 'devices.types.cooking.coffee_maker';
    /**
     * Устройство, которое умеет кипятить воду и/или делать чай.
     * Example: Умный чайник, термопот.
     */
    public const DEVICE_TYPE_COOKING_KETTLE = 'devices.types.cooking.kettle';
    /**
     * Устройство, которое выполняет функции мультиварки — приготовление пищи по заданным программам.
     * Example: Мультиварка.
     */
    public const DEVICE_TYPE_COOKING_MULTICOOKER = 'devices.types.cooking.multicooker';
    /**
     * Устройство, которое умеет открываться и/или закрываться.
     * Example: Дверь, ворота, окно, ставни. Для штор и жалюзи рекомендуется использовать отдельный тип devices.types.openable.curtain .
     */
    public const DEVICE_TYPE_OPENABLE = 'devices.types.openable';
    /**
     * Устройство, которое выполняет функцию штор.
     * Example: Шторы, жалюзи.
     */
    public const DEVICE_TYPE_OPENABLE_CURTAIN = 'devices.types.openable.curtain';
    /**
     * Устройство, которое умеет изменять влажность в помещении.
     * Example: Увлажнитель воздуха.
     */
    public const DEVICE_TYPE_HUMIDIFIER = 'devices.types.humidifier';
    /**
     * Устройство с функцией очистки воздуха.
     * Example: Очиститель воздуха, мойка воздуха.
     */
    public const DEVICE_TYPE_PURIFIER = 'devices.types.purifier';
    /**
     * Устройство, которое выполняет функцию пылесоса.
     * Example: Робот-пылесос.
     */
    public const DEVICE_TYPE_VACUUM_CLEANER = 'devices.types.vacuum_cleaner';
    /**
     * Устройство для стирки белья.
     * Example: Стиральная машина.
     */
    public const DEVICE_TYPE_WASHING_MACHINE = 'devices.types.washing_machine';
    /**
     * Устройство для мытья посуды.
     * Example: Посудомоечная машина.
     */
    public const DEVICE_TYPE_DISHWASHER = 'devices.types.dishwasher';
    /**
     * Устройство, которое выполняет функции утюга.
     * Example: Утюг, парогенератор.
     */
    public const DEVICE_TYPE_IRON = 'devices.types.iron';
    /**
     * Устройство, которое передает данные со свойств.
     * Example: Датчик температуры, датчик влажности, датчик открытия двери, датчик движения.
     */
    public const DEVICE_TYPE_SENSOR = 'devices.types.sensor';
    /**
     * Остальные устройства.
     * Example: Остальные устройства, не подходящие под типы выше.
     */
    public const DEVICE_TYPE_OTHER = 'devices.types.other';

    public $enableCsrfValidation = false;

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'authenticator' => [
                'class' => CompositeAuth::className(),
                'authMethods' => [
                    ['class' => HttpBearerAuth::className()],
                    [
                        'class' => QueryParamAuth::className(),
                        'tokenParam' => 'accessToken',
                    ],
                ],
                'optional' => [
                ],
            ],
            'exceptionFilter' => [
                'class' => ErrorToExceptionFilter::className(),
            ],
        ]);
    }

    public function actionAction()
    {
        /** @var User $identity */
        $identity = \Yii::$app->user->identity;

        $host = $identity->host;
        $token = $identity->token;

        $userId = \Yii::$app->user->id;
        $requestId = \Yii::$app->request->headers->get('X-Request-Id');
        $data = \Yii::$app->request->post();
        $devices = $data['payload']['devices'];
        foreach ($devices as $device) {
            if ($device['id'] === 'uniq_1') {
                $capabilities = $device['capabilities'];
                $type = $capabilities[0]['type'];
                $state = $capabilities[0]['state']['value'];
                $url
                    = "http://{$host}/api/scripts?action=evalFile&path=/alice.lua&token={$token}";
                \Yii::debug($url);
                file_get_contents($url);
            }
        }

        return [
            'request_id' => $requestId,
            'payload' => [
                'devices' => [
                    [
                        'id' => 'uniq_1',
                        'capabilities' => [
                            [
                                'type' => 'devices.capabilities.on_off',
                                'state' => [
                                    'instance' => 'on',
                                    'action_result' => [
                                        'status' => 'DONE',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function actionQuery()
    {
        $userId = \Yii::$app->user->id;
        $requestId = \Yii::$app->request->headers->get('X-Request-Id');

        return [
            'request_id' => $requestId,
            'payload' => [
                'devices' => [
                    [
                        'id' => 'uniq_1',
                        'capabilities' => [
                            [
                                'type' => 'devices.capabilities.on_off',
                                'state' => [
                                    'instance' => 'on',
                                    'value' => true,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        /** @var User $identity */
        $identity = \Yii::$app->user->identity;

        $host = $identity->host;
        $token = $identity->token;

        $userId = \Yii::$app->user->id;
        $requestId = \Yii::$app->request->headers->get('X-Request-Id');

        $client = new Client([
            'base_uri' => "http://{$host}/api/",
        ]);

        $result = $client->get('zigbee/devices', [
            'query' => [
                'token' => $token,
            ],
        ]);
        $slsDevices = json_decode((string) $result->getBody(), true);

        $devices = array_values(ArrayHelper::map($slsDevices, 'friendly_name',
            function ($data) {
                $manufacture = $this->getManufacture($data);
                $model = $this->getManufactureModel($data);

                $desc = 'Описание';
                $room = 'Спальня';
                $type = $this->getDeviceType($data);
                $id = $data['nwkAddr'];
                $name = "Неизвестно $manufacture $model";
                if ($type === self::DEVICE_TYPE_SWITCH) {
                    $name = "Выключатель $manufacture $model";
                }

//                if ($data[]){}

                return [
                    'id' => $id,
                    'name' => $name, //'Выключатель',
                    'description' => $desc, //'умная Выключатель',
                    'room' => $room,
                    'type' => $type,
                    'custom_data' => [
                        'manufacture_name' => $data['ManufName'],
                        'model_id' => $data['ModelId'],
                        'friendly_name' => $data['friendly_name'],
                    ],
                    'capabilities' => [
                        [
                            'type' => 'devices.capabilities.on_off',
                            'retrievable' => false,
                            'reportable' => false,
                            'parameters' => [
                                'split' => false,
                            ],
                        ],
                    ],
                    'properties' => [
                    ],
                    'device_info' => [
                        'manufacturer' => 'SLS devices',
                        'model' => 'custom',
                        'hw_version' => 'v1.0',
                        'sw_version' => 'v1.0',
                    ],
                ];
            }));

        return [
            'request_id' => $requestId,
            'payload' => [
                'user_id' => "$userId",
                'devices' => $devices,
            ],
        ];
    }

    private function getManufacture($data)
    {
        if (preg_match('(TZ3L|TZ3000|TS0043|TS0042|TS0041)', $data['ManufName']) !== false) {
            return 'TUYA';
        }

        return 'other';
    }

    private function getManufactureModel($data)
    {
        $manufacture = $this->getManufacture($data);
        if ($manufacture === 'TUYA') {
//            _TZ3000_46t1rvdu
            preg_match("/_(?'model'[^_]+)_(?'serial'[^_]+)/", $data['ManufName'], $matches);

            return $matches['model'] ?? "other";
        }

        return 'other';
    }

    private function getManufactureSerial($data)
    {
        $manufacture = $this->getManufacture($data);
        if ($manufacture === 'TUYA') {
//            _TZ3000_46t1rvdu
            preg_match("/_(?'model'[^_]+)_(?'serial'[^_]+)/", $data['ManufName'], $matches);

            return $matches['serial'] ?? "other";
        }

        return 'other';
    }

    private function getDeviceType($data)
    {
        $manufacture = $this->getManufacture($data);
        $model = $this->getManufactureModel($data);
        $serial = $this->getManufactureSerial($data);

        if ($manufacture === 'TUYA' && $model === 'TZ3000' && $serial === '46t1rvdu') {
            return self::DEVICE_TYPE_SWITCH;
        }

        return self::DEVICE_TYPE_OTHER;
    }
}
