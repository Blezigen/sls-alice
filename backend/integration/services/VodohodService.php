<?php

namespace integration\services;

use integration\models\Integration;
use Yii;
use yii\base\Model;
use yii\httpclient\Client;


/*
    https://vodohod.com/for-agencies/api-vodohod/

    Адрес документации: https://api-crs.vodohod.com/docs/agency/

    Логин для доступа к документации: agency_account
    Пароль для доступа к документации: Ny9Cm2DVFXNqAt3T5C1c
*/

class VodohodService extends \integration\services\base\ImportApiService
{
    public function init()
    {
        parent::init();

        $params = \Yii::$app->params['vodohod'];

        $this->baseurl = $params['baseurl'];
        $this->basepath = $params['basepath'];
        $this->login = $params['login'];
        $this->pass = $params['pass'];
        $this->company = $params['company'];

        $this->createCompany();
        $this->getAccessToken();
    }

    /*** Создание заказа ***/

    public function createOrder($id)
    {
        return false;

        $model = $this->getOrderByID($id);

        if (!$model) {
            return false;
        }

        // POST book - Бронирование/добавление каюты
        $bookData = [
            "room" => 50, // ID каюты.
            "accommodation" => 2, // Размещение - принимает от 1 до 4.
            "places" => [
                // Массив из [ID места] (поле places) => [ID тарифа].
                "50" => 50
            ],
            "childPlaces" => [
                // Массив из childPlaces [ID места] (поле children) => [ID тарифа].
                "52" => 52
            ],
            "infantPlaces" => [
                // Массив из infantPlaces [ID места] (поле infants) => [ID тарифа].
            ],
            // "order" => 50, // ID заказа, если надо прикрепить каюту к существующему заказу. Необязательный.
            // "bedConfiguration" => 1, // ID конфигурации кроватей для каюты. Необязательный.
        ];

        $tourists = [];

        if ($response = $this->post("book", $bookData)) {
            foreach ($tourists as $tourist) {
                // POST place - Посадка пассажиров на место в каюту

                $touristData = [
                    "order" => 50, // * ID заказа.
                    "room" => 50, // * ID каюты.
                    "place" => 50, // * ID места.
                    "firstName" => "Иван", // * Имя
                    "lastName" => "Попов", // * Фамилия
                    "patronymicName" => "Сергеевич", // Отчество или Middle name.
                    "email" => "testUser@mail.com", // Email
                    "phone" => "899912345678", // Телефон
                    "sex" => "1", // * Пол. 1 - мужской, 0 - женский.
                    "birthDate" => "21.12.2020", // * День рождения. Формат даты DD.MM.YYYY.
                    "nationality" => "1", // nationality
                    "address" => "г.Краснодар. ул.Ленинина", // * Адрес
                    "documentType" => 1, // * Тип документа: 1 - Паспорт РФ, 2 - Водительское удостоверение,3 - Иностранный документ ,4 - Заграничный паспорт ,5 - Свидетельство о рождении РФ ,6 - Студенческий билет ,7 - Удостоверение пенсионера ,8 - Удостоверение личности ,9 - Справка из школы.
                    "documentNumber" => "225566", // * Номер документа.
                    "documentIssueDate" => "21.12.1999", // * Дата выдачи документа. Формат даты DD.MM.YYYY.
                    "documentIssuerer" => "администрация г. Саров", // * Организация, которая выдала документ.
                    "documentSeries" => "1234" // * Серия документа.
                ];

                $this->post("place", $touristData);
            }
        }
    }

    public function confirmOrder($orderID)
    {
        // confirm
        $data = [
            "order" => $orderID // ID заказа, который надо подтвердить.
        ];

        return $this->post("confirm", $data);
    }

    public function cancel($orderID)
    {
        // cancel
        $data = [
            "order" => $orderID // ID заказа, который надо подтвердить.
        ];

        return $this->post("cancel", $data);
    }

    /*** Импорт данных ***/

    public function importCities()
    {
        $cities = $this->get("cities");

        foreach ($cities["data"] as $city) {
            $data = [
                "title" => $city["name"]
            ];

            $this->importCity($data, $city['id']);
        }

        return true;
    }

    public function importShips()
    {
        $ships = $this->get("motorships", [
            'filter[cruisesDatesFrom]' => strtotime("now")
        ]);

        foreach ($ships['data'] as $ship) {
            $this->prepareImportShip($ship);

            $motorship = $this->get("motorship", [
                'id' => $ship['id']
            ]);

            if ($motorship) {
                foreach ($motorship["roomClasses"] as $roomClass) {
                    $this->prepareImportClassCabin($roomClass);
                }
            }

            sleep(0.1);
        }

        return true;
    }

    public function importData()
    {
        $cruises = $this->get("cruises", [
            "filter[dateFrom]" => strtotime("now")
        ]);

        foreach ($cruises["data"] as $cruise) {
            $cruiseId = $cruise["id"];
            $tour = $this->prepareImportTour($cruise);

            foreach ($cruise["route"] as $route) {
                $this->prepareImportShipNavigation($route, $tour->id);
            }

            $cabins = $this->get("cabins", ["id" => $cruiseId]);

            if ($cabins) {
                foreach ($cabins["data"] as $cabin) {
                    $this->prepareImportCabin($cabin);
                    $prices = $this->get("cabin", ["id" => $cabin["id"]]);
                    // var_dump($prices);
                    foreach ($prices as $price) {
                        // var_dump($price["name"]);
                        $this->prepareImportPrice($price, $cabin, $tour->id);
                    }
                }
            }
            // die();
            sleep(1);
        }

        return true;
    }

    /*
        Импорт корабля
    */
    protected function prepareImportShip($entry)
    {
        $data = [
            "title" => $entry["name"],
        ];

        return $this->importShip($data, $entry['id']);
    }

    /*
        Импорт навигации
    */
    protected function prepareImportShipNavigation($entry, $tourId = null)
    {
        $city = $this->importCity([
            "title" => $entry["name"]
        ]);

        if (!$city)
            return false;

        $data = [
            "ship_id" => $this->shipId,
            "city_id" => $city->id,
            "arrival_dt" => $entry["in"],
            "departure_dt" => $entry["out"],
            "tour_id" => $tourId
        ];

        return $this->importShipNavigation($data, $entry["id"]);
    }

    /*
        Импорт классов кабин
    */
    protected function prepareImportClassCabin($entry)
    {
        $title = $entry['name'];

        if (strpos(mb_strtolower($title), "одноместн") !== false) {
            $max_place_base = 1;
        } elseif (strpos(mb_strtolower($title), "двухмест") !== false) {
            $max_place_base = 2;
        } elseif (strpos(mb_strtolower($title), "трехмест") !== false) {
            $max_place_base = 3;
        } elseif (strpos(mb_strtolower($title), "четырехмест") !== false) {
            $max_place_base = 4;
        } elseif (strpos(mb_strtolower($title), "пятимест") !== false) {
            $max_place_base = 5;
        } else {
            $max_place_base = 1;
        }

        $data = [
            'max_place_base' => $max_place_base,
            // 'max_place_advance' => ,
            'title' => $title,
            'ship_id' => $this->shipId,
        ];

        return $this->importClassCabin($data, $entry['id']);
    }

    /*
        Импорт кабин
    */
    protected function prepareImportCabin($cabin)
    {
        $data = [
            "number" => $cabin["number"],
            "description" => $cabin["side"],
            "ship_id" => $this->shipId
        ];

        return $this->importCabin($data, $cabin['id']);
    }

    /*
        Импорт цен
    */
    protected function prepareImportPrice($price = null, $cabin = null, $tourId = null)
    {
        if (!$price || !$cabin || !$tourId) {
            return;
        }

        $cabinClass = Integration::find()
            ->where([
                "type" => "ShipCabinClass",
                "service" => $this->company,
                "external_cid" => $cabin["class"]["id"]
            ])
            ->one();

        if (!$cabinClass)
            return;

        foreach ($price["tariffs"] as $tariff) {
            $data = [
                "tour_id" => $tourId,
                "ship_cabin_class_id" => $cabinClass->internal_cid,
                "price" => $tariff["price"],

                "additional_price" => 0,
                "increasing_percent" => 0,
            ];

            $this->importPrice($data, $tariff["id"]);
        }


        return true;
    }

    /*
        Импорт тура
    */
    protected function prepareImportTour($entry)
    {
        $ship = $this->prepareImportShip($entry["motorship"]);

        if (!$ship) {
            return false;
        }

        $data = [
            "ship_id" => $ship->id,
            "company_id" => $this->companyId,
            "comment" => $entry["name"],
            "departure_dt" => $entry["dateStart"],
            "arrival_dt" => $entry["dateEnd"],
        ];

        return $this->importTour($data, $entry["id"]);
    }

    protected function auth()
    {
        $response = $this->client()->post('/security/authorise', [
            'login' => $this->login,
            'password' => $this->pass,
        ])->send();

        $data = json_decode($response->content, true);
        $this->session()->set('vodohod', $data['result']);

        return $this->getAccessToken();
    }

    protected function session()
    {
        return Yii::$app->session;
    }

    public function getAccessToken()
    {
        $login = $this->login;
        $pass = $this->pass;

        $response = $this->cache()->getOrSet("vodohod_access-token", function () use ($login, $pass) {
            return $this->client()->post('/security/authorise', [
                'login' => $login,
                'password' => $pass,
            ])->send();
        }, 3600);

        $data = json_decode($response->content, true);
        return $data['result']["accessToken"]["token"];
    }

    public function getBasicParams()
    {
        return [];
    }
}
