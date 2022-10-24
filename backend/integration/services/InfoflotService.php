<?php

namespace integration\services;

use integration\models\Integration;

/*
    https://booking.infoflot.com/workingWithAgencies

    Адрес документации: https://restapi.infoflot.com/docs

    Отправляйте АПИ-ключ параметром в командной строке ?key=XXXXXXXXX или заголовком запроса x-api-key
*/

/*
    Комментарий:
        инфофлот забирает у нас данные через XML шлюз https://rdt.sputnik-germes.ru/xml
        Затем, по заявке от клиента, заходят к нам в систему и бронируют
        Онлайн бронирования нет
*/

class InfoflotService extends \integration\services\base\ImportApiService
{
    protected int $countryId = 1; // Россия

    public function init()
    {
        parent::init();

        $params = \Yii::$app->params['infoflot'];

        $this->baseurl = $params['baseurl'];
        $this->token = $params['token'];
        $this->company = $params['company'];

        $this->createCompany();
    }

    /*** Создание заказа ***/

    public function createOrder($id)
    {
        return false;

        $model = $this->getOrderByID($id);

        if (!$model) {
            return false;
        }

        // POST requests - Создание заявки

        $services = [];
        $passengers = [];

        $services[] = [
            "service_type" => 10, // Тип услуги. Указывается из списка констант: 0 - Круиз, 1 - Экскурсия, 2 - Виза, 3 - Трансфер, 4 - Авивбилет, 5 - Ж/д билет, 6 - Страховка, 7 - Отель, 8 - Сборы, 9 - Аренда, 10 - Сертификат, 20 - Прочее ( cruise_id - Указывается только в услуге с криузом. Для услуг с другим service_type этот параметр будет проигнорирован )
            "cruise_id" => 20,
        ];

        foreach ([] as $tourist) {
            $passengers[] = [
                "passenger_type" => 10, // тип размещения пассажира. Указывается из списка констант: 0 - взрослое, 1 - взрослое+детское, 2 - детское, 3 - свободное, 4 - одноместное
                "cabin_id" => 234, // идентификатор бронуруемой каюты
                "cabin_name" => "123", // название бронируемой каюты
                "first_name" => "Иван", // имя
                "last_name" => "Иванов", // фамилия
                "middle_name" => "Иванович", // отчество
                "passport_series" => "9999", // серия паспорта
                "passport_number" => "999999" // номер паспорта
            ];
        }

        $bookData = [
            "services" => $services,
            "passengers" => $passengers,
        ];

        return $this->post("requests", $bookData);
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
        $ships = $this->get("ships-active");

        foreach ($ships['data'] as $ship) {
            $this->prepareImportShip($ship);

            foreach ($ship['cabins'] as $cabin) {
                $this->prepareImportCabin($cabin);
            }

            sleep(1);
        }

        return true;
    }

    public function importData()
    {
        $cruises = $this->get("cruises", [
            "startCountry" => $this->countryId
        ]);

        if (!$cruises)
            return false;


        foreach ($cruises["data"] as $cruise) {
            $cruiseId = $cruise["id"];
            $tour = $this->prepareImportTour($cruise);

            $cruiseData = $this->get("cruises/{$cruiseId}");

            foreach ($cruiseData["timetable"] as $route) {
                $this->prepareImportShipNavigation($route, $tour->id);
            }

            $cabins = $this->get("cruises/{$cruiseId}/cabins");

            foreach ($cabins['cabins'] as $cabinId => $cabin) {
                $cabinTypeId = $cabin["type_id"];
                $cabinPrices = $cabins["prices"];

                if (array_key_exists($cabinId, $cabinPrices)) {
                    $price = $cabinPrices[$cabinId];
                } elseif (array_key_exists($cabinTypeId, $cabinPrices)) {
                    $price = $cabinPrices[$cabinTypeId];
                } else {
                    $price = null;
                }

                $this->prepareImportPrice($price, $cabin, $tour->id);
            }

            die();
            sleep(1);
        }

        return true;
    }

    /*
        Импорт навигации
    */
    protected function prepareImportShipNavigation($entry, $tourId = null)
    {
        $city = $this->importCity([
            "title" => $entry["city"]["name"]
        ]);

        if (!$city)
            return false;

        $data = [
            "ship_id" => $this->shipId,
            "city_id" => $city->id,
            "arrival_dt" => $entry["dateArrival"],
            "departure_dt" => $entry["dateDeparture"],
            "tour_id" => $tourId
        ];

        return $this->importShipNavigation($data, $entry["id"]);
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
        Импорт кабин
    */
    protected function prepareImportCabin($entry)
    {
        $data = [
            'max_place_base' => $entry["places"]["main"],
            'max_place_advance' => $entry["places"]["additional"],
            'title' => $entry["typeName"],
            'ship_id' => $this->shipId,
        ];

        $classCabin = $this->importClassCabin($data, $entry['typeId']);

        if (!$classCabin) {
            return false;
        }

        if (strlen($entry["name"]) > 10) {
            return false;
        }

        $data = [
            "number" => $entry["name"],
            "ship_id" => $this->shipId,
            "ship_cabin_class_id" => $classCabin->id,
        ];

        return $this->importCabin($data, $entry['id']);
    }

    /*
        Импорт цен
    */
    protected function prepareImportPrice($price = null, $cabin = null, $tourId = null)
    {
        if (!$price || !$cabin || !$tourId) {
            return false;
        }

        $cabinClass = Integration::find()
            ->where([
                "type" => "ShipCabinClass",
                "service" => $this->company,
                "external_cid" => $cabin["type_id"]
            ])
            ->one();

        if (!$cabinClass)
            return false;

        foreach ($price["prices"]["main_bottom"] as $tariffType => $tariffPrice) {
            $data = [
                "tour_id" => $tourId,
                "ship_cabin_class_id" => $cabinClass->internal_cid,
                "price" => $tariffPrice,

                "additional_price" => 0,
                "increasing_percent" => 0,
            ];

            $this->importPrice($data);
        }

        return true;
    }

    /*
        Импорт тура
    */
    protected function prepareImportTour($entry)
    {
        $ship = $this->prepareImportShip($entry["ship"]);

        if (!$ship) {
            return false;
        }

        $data = [
            "ship_id" => $ship->id,
            "company_id" => $this->companyId,
            "comment" => $entry["beautifulName"],
            "departure_dt" => $entry["dateStart"],
            "arrival_dt" => $entry["dateEnd"],
        ];

        return $this->importTour($data, $entry['id']);
    }

    public function getAccessToken()
    {
        return null;
    }

    public function getBasicParams()
    {
        return [
            "key" => $this->token
        ];
    }
}
