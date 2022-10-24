<?php

namespace integration\services;

use yii\base\Model;
use common\models\Order;
use common\models\IdentityDocument;
use yii\web\HttpException;

/*
    Документация: https://docs.google.com/document/d/1jG9XNdy7Tb48EebttTD3Gs1oAbuhFI9uPTbHUxxobDU/edit
*/

class AlfastrahService extends Model
{
    private string $userId;
    private string $userLogin;
    private string $userPSW;

    private string $baseProgram = "Классик";
    private string $baseRisk = "Отмена поездки";
    private string $baseCountry = "RUSSIA";
    private string $baseRiskVariant = 'Безвизовые страны без франшизы';

    private int $amountAtRisk = 1500;
    private string $amountCurrency = 'USD';

    public $programUid;
    public $countryUid;
    public $riskUid;
    public $riskVariantUid;

    private string $wsdlUrl;

    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    public function init()
    {
        parent::init();

        $params = \Yii::$app->params['alfastrah'];

        $this->wsdlUrl = $params['wsdlUrl'];
        $this->userId = $params['userId'];
        $this->userLogin = $params['userLogin'];
        $this->userPSW = $params['userPSW'];
    }

    protected function client()
    {
        return new \SoapClient($this->wsdlUrl);
    }

    protected function getParams($params = [])
    {
        $baseParams = [
            'agentUid' => $this->userId
        ];

        if ($this->programUid) {
            $baseParams['programUid'] = $this->programUid;
        }

        if ($this->countryUid) {
            $baseParams['countryUid'] = $this->countryUid;
        }

        if ($this->riskUid) {
            $baseParams['riskUid'] = $this->riskUid;
        }

        return [
            'parameters' => array_merge(
                $baseParams,
                $params
            )
        ];
    }

    /*
        Запрос программ страхования доступных агенту

        Программы A, B, C, D, E – оформляются на определенный срок поездки. От 1 до 365 дней. 
        Программа VIP  - всегда оформляется на 1 год (365 или 366 дней). 
        Программа MULTI. Оформляется на срок от 1 до 366 дней. При этом отдельно указывается сколько дней будет действовать страховка. 
    */
    public function GetInsuranceProgramms($params = [])
    {
        return $this->client()->GetInsuranceProgramms($this->getParams($params))->GetInsuranceProgrammsResult->insuranceProgramm;
    }

    /*
        Запрос списков рисков доступных к страхованию в выбранной программе
        
        В запросе необходимо передавать параметр «Программа страхования».
    */
    public function GetRisks($programmUid)
    {
        $this->setProgramUid($programmUid);

        return $this->client()->GetRisks($this->getParams())->GetRisksResult->risk;
    }

    /*
        Запрос Стран

        При запросе Справочника стран необходимо передать параметр «Программа страхования». В этом случае будет возвращен справочник стран применимых для выбранной программы.
    */
    public function GetCountries($programmUid)
    {
        $this->setProgramUid($programmUid);

        return $this->client()->GetCountries($this->getParams())->GetCountriesResult->country;
    }

    /*
        Запрос Условия Спорт/Работа
    */
    public function GetAdditionalConditions()
    {
        return $this->client()->GetAdditionalConditions($this->getParams())->GetAdditionalConditionsResult->additionalCondition;
    }

    /*
        Метод возвратит список страховых сумм доступных к страхованию. При вызове метода необходимо передать «Программу страхования», «Выбранную страну». Можно дополнить запрос параметром риск. И вызвать его для каждого риска отдельно.

        - Риск Медицинские расходы могут быть застрахованы только на 15000, 230000, 50000 и 100000. (EUR или USD)
        - Риск Несчастный случай может быть застрахован на 1000 (EUR или USD)
        - Риск «Отмена поездки» может быть застрахован от 500 до 3500 (EUR или USD)
        - Если у риска есть несколько вариантов, то будет заполнены колонки Variant и VariantUID. При создании полиса «Вариант» надо передавать, только если у риска существуют несколько вариантов.
    */
    public function GetStruhSum()
    {
        return $this->client()->GetStruhSum($this->getParams())->GetStruhSumResult->struhSum;
    }

    /*
        Запрос Списка доступных Франшиз

        Данный метод не обязательный. В большинстве случаев у Агента настроен один вариант франшиз. 
    */
    public function GetFransize($params = [])
    {
        return [];
    }


    /*
        Запрос Ассистента (Дополнительные методы)

        С указанием выбранной страны. Используется только в случае если у агента несколько ассистентов и если агент будет сам формировать печатную форму полиса
    */
    public function GetAssistance($params = [])
    {
        return [];
    }

    /*
        Запрос списка доступных территорий (Дополнительные методы)

        Может быть использовано при необходимости отразить в пользовательском интерфейсе описания территории действия страхового полиса
    */
    public function GetTerritory($params = [])
    {
        return [];
    }

    /*
        Запрос списка доступных валют (Дополнительные методы)

        Метод возвратит значения: RUR, USD, EUR
    */
    public function GetCurrency($params = [])
    {
        return [];
    }

    /*
        Запрос доступных вариантов (Дополнительные методы)
    */
    public function GetVariant($params = [])
    {
        return [];
    }

    /*
        Запрос Условий Скидки/Надбавки (Дополнительные методы)
    */
    public function GetAdditionalConditions2($params = [])
    {
        return [];
    }

    /*
        Расчет полиса
        
            Расчет полиса производится методом NewPolicty. В качестве параметра «Operation» необходимо передать значение «Calculate».

            В ответе будет получен XML содержавший данные по стоимости запрошенного полиса.
            При операции Расчет номер полису не присваивается и в системе Travel Insurance полиса не создается.

        Регистрация полиса

            Регистрация нового полиса производится методом NewPolicty. В качестве параметра «Operation» необходимо передать значение «Create», «Draft».

            В ответе будет получен XML содержащий все информацию по созданному полису.

            По результату операции по созданию полиса в системе Travel Insurance будет создан полис находящийся в статусе «Акцептован» для операции Create и «Черновик» для операции Draft. 
            Для перевода полиса в статусе Черновик в статус Акцептован  необходимо вызвать метод SetAcceptPolicy.
    */
    public function NewPolicty($operation, $common, $insureds, $risks)
    {
        // operation - Calculate, Create, Draft, Registration, Replace

        $params = [
            'policy' => [
                'common' => array_merge(
                    $common,
                    [
                        'userId' => $this->userId,
                        'userLogin' => $this->userLogin,
                        'userPSW' => $this->userPSW,
                    ],
                    ['operation' => $operation]
                ),
                'insureds' => $insureds,
                'risks' => $risks,
            ]
        ];

        $result = $this->client()->NewPolicty($params)->NewPolictyResult;

        // if (isset($result->common->policyUID)) {
        //     $this->SetPayPolicy($result->common->policyUID);
        // }

        return $result ? $result->common : null;
    }

    /*
        Для перевода полиса в статусе Черновик в статус Акцептован  необходимо вызвать метод SetAcceptPolicy
    */
    public function SetAcceptPolicy()
    {
        // return $this->client()->SetAcceptPolicy();
    }

    /*
        Оплата банковскими картами. Отсылка email страхователю.
    */
    public function SetPayPolicy($number)
    {
        $params = [
            'parameters' => [
                'agentUid' => $this->userId,
                'agentLogin' => $this->userLogin,
                'agentPassword' => $this->userPSW,
                'number' => $number,
            ]
        ];

        return $this->client()->SetPayPolicy($params);
    }


    /*
        Формирование данных для расчета полиса исходя из заказа
    */
    public function calculatePolicty($orderId)
    {
        return $this->createPolicty($orderId, 'Calculate');
    }

    /*
        Формирование данных для полиса исходя из заказа
    */
    public function createPolicty($orderId, $operation = "Create")
    {
        $orderData = $this->prepareDataByOrderID($orderId);

        $departure_dt = $orderData['departure_dt'];
        $arrival_dt = $orderData['arrival_dt'];
        $buyer = $orderData['buyer'];
        $insureds = $orderData['insureds'];

        if (!$this->programUid) $this->setProgramUid();
        if (!$this->countryUid) $this->setCountryUid();
        if (!$this->riskUid) $this->setRiskUid();
        if (!$this->riskVariantUid) $this->setRiskVariantUid();

        $common = array_merge(
            [
                'countryUID' => $this->countryUid,
                'insuranceProgrammUID' => $this->programUid,
                'policyPeriodFrom' => $departure_dt,
                'policyPeriodTill' => $arrival_dt,
            ],
            $buyer
        );

        $risk = [
            'riskUID' => $this->riskUid,
            'amountAtRisk' => $this->amountAtRisk,
            'amountCurrency' => $this->amountCurrency,
        ];

        if ($this->riskVariantUid) {
            $risk['riskVariantUID'] = $this->riskVariantUid;
        }

        $risks = [$risk];

        return $this->NewPolicty($operation, $common, $insureds, $risks);
    }

    public function setProgramUid($uid = null)
    {
        if (!$uid) {
            // Поиск базовой программы
            $list = $this->GetInsuranceProgramms();

            foreach ($list as $item) {
                if ($item->insuranceProgrammName == $this->baseProgram) {
                    $uid = $item->insuranceProgrammUID;
                }
            }
        }

        $this->programUid = $uid;
    }

    public function setCountryUid($uid = null)
    {
        if (!$uid) {
            // Поиск базовой страны
            $list = $this->GetCountries($this->programUid);

            foreach ($list as $item) {
                if ($item->countryName == $this->baseCountry) {
                    $uid = $item->countryUID;
                }
            }
        }

        $this->countryUid = $uid;
    }

    public function setRiskUid($uid = null)
    {
        if (!$uid) {
            // Поиск базового риска
            $list = $this->GetRisks($this->programUid);

            foreach ($list as $item) {
                if ($item->risk == $this->baseRisk) {
                    $uid = $item->riskUID;
                }
            }
        }

        $this->riskUid = $uid;
    }

    public function setRiskVariantUid($uid = null)
    {
        if (!$uid) {
            // Поиск базового варианта риска
            $list = $this->GetStruhSum();

            foreach ($list as $item) {
                if (
                    $item->variant == $this->baseRiskVariant &&
                    $item->valutaCode == $this->amountCurrency &&
                    $item->strahSummTo == $this->amountAtRisk
                ) {
                    $uid = $item->variantUid;
                }
            }
        }

        $this->riskVariantUid = $uid;
    }


    protected function prepareDataByOrderID($orderID)
    {
        $order = Order::find()
            ->byId($orderID)
            ->one();

        if (!$order) {
            throw new HttpException(404, 'Заказ не найден');
        }

        $insureds = []; // туристы
        $buyer = null; // покупатель

        $departure_dt = date("Y-m-d\TH:i:s", strtotime($order->tour->departure_dt));
        $arrival_dt = date("Y-m-d\TH:i:s", strtotime($order->tour->arrival_dt));

        if (!$departure_dt || !$arrival_dt) {
            throw new HttpException(400, 'Не указан $departure_dt или $arrival_dt');
        }

        $documentId = $order->contractor->identity_document_id;

        $document = IdentityDocument::find()
            ->byId($documentId)
            ->one();

        if (!$document) {
            throw new HttpException(404, 'Информация о заказчике отсутствует');
        }

        $birthDate = $document->birth_date ? date("Y-m-d", strtotime($document->birth_date)) : null;

        $buyer = [
            'fio'               => "{$document->last_name} {$document->first_name} {$document->third_name}",
            'addressTel'        => "{$document->phone}",
            'eMail'             => "{$document->email}",
            'dateOfBirth'       => "{$birthDate}",
            'documentNumber'    => "{$document->number}",
            'documentSerial'    => "{$document->serial}",
            'documentType'      => 21, // паспорт
        ];

        $cabins = $order->orderCabins;

        foreach ($cabins as $cabin) {
            $places = $cabin->orderPlaces;

            foreach ($places as $place) {
                if ($place->identity_document_id) {
                    $documentId = $place->identity_document_id;

                    $document = IdentityDocument::find()
                        ->byId($documentId)
                        ->one();

                    if (!$document) {
                        throw new HttpException(404, 'Информация о туристе отсутствует');
                    }

                    $birthDate = $document->birth_date ? date("Y-m-d", strtotime($document->birth_date)) : null;

                    $insureds[] = [
                        'fio'           => "{$document->last_name} {$document->first_name} {$document->third_name}",
                        'passport'      => "{$document->serial} {$document->number}",
                        'dateOfBirth'   => "{$birthDate}"
                    ];
                }
            }
        }

        return [
            'departure_dt' => $departure_dt,
            'arrival_dt' => $arrival_dt,
            'buyer' => $buyer,
            'insureds' => $insureds,
        ];
    }
}
