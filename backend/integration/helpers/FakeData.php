<?php

namespace integration\helpers;

class FakeData
{
    public function getUsers($count = null)
    {
        if (!$count)
            $count = rand(1, 30);

        $result = [];

        for ($i = 0; $i < $count; $i++) {
            $result[] = $this->getUser();
        }

        return $result;
    }

    public function getUser()
    {
        return [
            'fio' => $this->faker()->name(),
            'surname' => $this->faker()->lastName(),
            'name' => $this->faker()->firstName(),
            'dateOfBirth' => $this->faker()->date(),
            'addressTel' => $this->faker()->numerify('(9##) ###-##-##'),
            // 'eMail' => $this->faker()->email(),
            'eMail' => "i@rdbx.ru",
            'passport' => $this->faker()->numerify('#### ######'),
            'documentNumber' => $this->faker()->numerify('######'),
            'documentSerial' => $this->faker()->numerify('####'),
            'documentType' => 21,
        ];
    }

    public function getMintransUser()
    {
        $day = 7;
        $day2 = 9;

        $rand = rand(0, 1);
        $tourist = $rand;
        $genders = ['M', 'F'];

        date_default_timezone_set('Europe/Moscow');

        $departDate = date('c', strtotime("+{$day} day"));
        $arriveDate = date('c', strtotime("+{$day2} day"));
        $registerTimeIS = date('c');
        $buyDate = $tourist ? date('c') : '';

        return [
            'surname' => $this->faker()->lastName(),
            'name' => $this->faker()->firstName(),
            'patronymic' => 'NA',
            'birthday' => $this->faker()->date(),
            // 'birthPlace' => '',
            'docType' => 0,
            'docNumber' => $this->faker()->numerify('##########'),
            'documentAdditionalInfo' => '',
            'departPlace' => 'Тест1',
            'arrivePlace' => 'Тест2',
            'routeType' => 0,
            'departDate' => $departDate,
            'citizenship' => 'RUS',
            'gender' => $genders[$rand],
            'recType' => $tourist ? 1 : 0,
            'rank' => $tourist ? '' : 'captain',
            'operationType' => $tourist ? 1 : 50,
            'operatorId' => 31038,
            'route' => 'Saint Petersburg - Moscow',
            'reservedSeatsCount' => $tourist ? "2" : '',
            'buyDate' => $buyDate,
            'termNumOrSurname' => $tourist ? 'NA' : '',
            'arriveDate' => $arriveDate,
            // 'arriveDateFact' => 'Arrive Date Fact',
            // 'deckNumber' => '',
            // 'roomNumber' => '',
            'shipClass' => 2,
            'shipNumber' => "12345",
            'shipName' => 'MV MINERVA',
            'flagState' => 'RU',
            'registerTimeIS' => $registerTimeIS,
            'operatorVersion' => 20,
        ];
    }

    private function faker()
    {
        return \Faker\Factory::create('ru_RU');
    }
}

// $faker->inn10()
// echo $faker->kpp();
// echo $faker->bank();