<?php

namespace integration\services;

class OdataImportService extends \integration\services\base\ImportApiService
{
    public function init()
    {
        parent::init();

        $this->company = "1C";
        $this->createCompany(false);
    }

    public function getAccessToken()
    {
        return null;
    }

    public function getBasicParams()
    {
        return [];
    }

    public function prepareShip($entry)
    {
        $data = [
            "title" => $entry->{"Description"},
        ];

        return $data;
    }

    public function prepareOrder($entry)
    {
    }

    public function preparePayment($entry)
    {
    }

    public function prepareTour($entry)
    {
        if (!$this->shipId) {
            return false;
        }

        $data = [
            "ship_id" => $this->shipId,
            // "company_id" => $this->companyId,
            "comment" => $entry->{"Description"},
            "departure_dt" => $entry->{"ДатаНачала"},
            "arrival_dt" => $entry->{"ДатаОкончания"},
        ];

        return $data;
    }

    public function prepareShipNavigations($entry, $shipId, $tourId)
    {
        $data = [];

        $tourTitle = $entry->{"Description"};
        $tourTitle = str_replace([".", " -"], ["", "_"], $tourTitle);

        $tourTitleArr = explode("-", $tourTitle);

        foreach ($tourTitleArr as $title) {
            $title = explode("(", $title);
            $title = trim($title[0]);

            $city = $this->importCity([
                "title" => $title
            ]);

            if (!$city)
                return false;

            $data[] = [
                "ship_id" => $shipId,
                "city_id" => $city->id,
                // "arrival_dt" => $entry["dateArrival"],
                // "departure_dt" => $entry["dateDeparture"],
                "tour_id" => $tourId
            ];
        }

        return $data;
    }



    public function prepareIdentityDocument($data)
    {
        $fio = trim($data->{"НаименованиеПолное"});
        $passport = trim($data->{"ДокументУдостоверяющийЛичность"});

        $fio = explode(" ", trim($fio));

        $last_name = null;
        $first_name = null;
        $third_name = null;

        if (count($fio) == 3) {
            list($last_name, $first_name, $third_name) = $fio;
        }

        if (count($fio) == 2) {
            list($last_name, $first_name) = $fio;
        }

        $attr = [
            "last_name" => $last_name,
            "first_name" => $first_name,
            "third_name" => $third_name
        ];

        if (!$first_name) {
            var_dump($data);
            die();
        }

        $passportArr = explode(" ", $passport);

        foreach ($passportArr as $key => $value) {
            $nextKey = $key + 1;
            $value = mb_strtolower($value);

            switch ($value) {
                case 'серия':
                    $attr["serial"] = intval($passportArr[$nextKey]) . "";
                    break;

                case 'номер':
                case '№':
                    $attr["number"] = intval($passportArr[$nextKey]) . "";
                    break;
            }

            if (preg_match("/^(0[1-9]|[1-2][0-9]|3[0-1]).(0[1-9]|1[0-2]).[0-9]{4}$/", $value)) {
                $attr["issue_date"] = date("Y-m-d", strtotime($value));
            }
        }

        $passportArr = explode(",", $passport);

        if (count($passportArr) == 2 && isset($attr["issue_date"])) {
            $issue = str_replace("выдан", "", $passportArr[1]);
            $issue = trim($issue);
            $issueArr = explode(" ", $issue);
            $issueRes = [];

            foreach ($issueArr as $value) {
                if (preg_match("/^(0[1-9]|[1-2][0-9]|3[0-1]).(0[1-9]|1[0-2]).[0-9]{4}$/", $value)) {
                    break;
                }

                $issueRes[] = $value;
            }

            $attr["issued"] = implode(" ", $issueRes);
        }

        return $attr;
    }

    public function prepareCompany($dataCompany)
    {
        $companyAttr = [
            'title' => $dataCompany->{"НаименованиеПолное"},
            'inn' => $dataCompany->{"ИНН"},
            'kpp' => $dataCompany->{"КПП"},
        ];
        $city = null;

        if ($dataCompany->{"КонтактнаяИнформация"}) {
            foreach ($dataCompany->{"КонтактнаяИнформация"} as $contact) {
                if ($contact->{"Тип"} == "Телефон") {
                    $temp = [
                        "phone" => $contact->{"Представление"}
                    ];

                    $companyAttr = array_merge($companyAttr, $temp);

                    continue;
                }

                if ($contact->{"Тип"} == "Адрес" && !$city) {

                    if (!$contact->{"Город"} && $contact->{"Регион"}) {
                        $contact->{"Город"} = $contact->{"Регион"};
                    }

                    $city = $this->importCity(["title" => $contact->{"Город"}]);

                    if (!$city) {
                        continue;
                    }

                    $temp = [
                        "legal_address" => $contact->{"Представление"},
                        "actual_address" => $contact->{"Представление"},
                        "city_id" => $city->id
                    ];

                    $companyAttr = array_merge($companyAttr, $temp);

                    continue;
                }
            }
        }

        return $companyAttr;
    }
}
