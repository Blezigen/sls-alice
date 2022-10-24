<?php

use common\models\ShipNavigation;
use integration\services\RussiatourismService;

$navigations = $tour->navigations;
$contractor = $order->contractor;

$startNavigation = $navigations ? $navigations[0] : null;
$endNavigation = $navigations ? $navigations[count($navigations) - 1] : null;

$startNavigation = ShipNavigation::findOne(1);
$endNavigation = ShipNavigation::findOne(2);

$departure_dt = $tour->departure_dt;
$arrival_dt = $tour->arrival_dt;

if ($departure_dt >= $arrival_dt) {
    $departure_temp = $departure_dt;
    $departure_dt = $arrival_dt;
    $arrival_dt = $departure_temp;
}

$contractDate = $order->created_at;

if ($contractDate > $departure_dt) {
    $contractDate = $departure_dt;
}

?>
<CreateVoucherRequest xmlns="urn://artefacts-russiatourism-ru/services/message-exchange/types/CreateVoucher">
    <Voucher>
        <number><?= $number ?></number>
        <status>RESERVED</status>
        <tripStartDate><?= date("Y-m-d", strtotime($departure_dt)) ?></tripStartDate>
        <tripEndDate><?= date("Y-m-d", strtotime($arrival_dt)) ?></tripEndDate>
        <?php /*
         <TourAgent>
             <inn>1234567899</inn>
         </TourAgent> */ ?>
        <comment>тестовый комментарий</comment>
        <VoucherType>
            <code>cruise</code>
        </VoucherType>
        <Order>
            <Customer>
                <?php /*
                <CompanyCustomer>
                    <name>"ООО Ваш супер тур"</name>
                    <inn>9876543210</inn>
                    <headLastName>Александров</headLastName>
                    <headFirstName>Александр</headFirstName>
                    <headPatronymic>Алексанрович</headPatronymic>
                    <LocatedCountry>
                        <code>RU</code>
                    </LocatedCountry>
                    <phoneNumber>849565985</phoneNumber>
                    <email>super@turvash.ru</email>
                    <address>Москва ул. Иванова 1</address>
                </CompanyCustomer>

                <SoleProprietorCustomer>
                    <lastName>Иванов</lastName>
                    <firstName>Иван</firstName>
                    <patronymic>Иванович</patronymic>
                    <IdentityDocumentType>
                        <code>RU01001</code>
                    </IdentityDocumentType>

                    <identityDocumentSeriesNumber>3608256968</identityDocumentSeriesNumber>
                    <IdentityDocumentIssueCountry>
                        <code>RU</code>
                    </IdentityDocumentIssueCountry>
                    <phoneNumber>89171648978</phoneNumber>
                    <email>ivann@ivanov.ru</email>
                    <inn>123456789012</inn>
                    <address>Самара, ул. Иванова дом 1. кв 1.</address>
                </SoleProprietorCustomer>
                */ ?>
                <?php if ($tourist = $contractor->identityDocument) : ?>
                    <IndividualCustomer>
                        <lastName><?= $tourist->last_name ?></lastName>
                        <firstName><?= $tourist->first_name ?></firstName>
                        <patronymic><?= $tourist->third_name ?></patronymic>
                        <IdentityDocumentType>
                            <code>RU01001</code>
                        </IdentityDocumentType>
                        <identityDocumentSeriesNumber><?= $tourist->serial ?><?= $tourist->number ?></identityDocumentSeriesNumber>
                        <IdentityDocumentIssueCountry>
                            <code>RU</code>
                        </IdentityDocumentIssueCountry>
                        <phoneNumber><?= $tourist->phone ?></phoneNumber>
                        <email><?= $tourist->email ?></email>
                        <birthDate><?= $tourist->birth_date ?></birthDate>
                        <address><?= $contractor->fact_address ?></address>
                    </IndividualCustomer>
                <?php
                elseif ($company = $contractor->company) :
                    $director = explode(" ", $company->director_full_name);
                    list($headLastName, $headFirstName, $headPatronymic) = $director;
                ?>
                    <CompanyCustomer>
                        <name><?= $company->title ?></name>
                        <?php /*<inn><?= $company->inn ?></inn>*/ ?>
                        <inn>1234567890</inn>
                        <headLastName><?= $headLastName ?></headLastName>
                        <headFirstName><?= $headFirstName ?></headFirstName>
                        <headPatronymic><?= $headPatronymic ?></headPatronymic>
                        <LocatedCountry>
                            <code>RU</code>
                        </LocatedCountry>
                        <phoneNumber><?= $company->phone ?></phoneNumber>
                        <email><?= $company->email ?></email>
                        <address><?= $company->legal_address ?></address>
                    </CompanyCustomer>
                <?php else : ?>
                <?php endif; ?>
            </Customer>
            <contractNumber><?= $order->id ?></contractNumber>
            <contractDate><?= date("Y-m-d", strtotime($contractDate)) ?></contractDate>
            <showCosts>true</showCosts>
            <cost><?= $order->_total_price ?></cost>
        </Order>
        <?php if ($startNavigation) : ?>
            <LeavingCity>
                <code><?= RussiatourismService::getCityCodeById($startNavigation->city_id) ?></code>
            </LeavingCity>
        <?php endif; ?>
        <?php if ($endNavigation) : ?>
            <DestinationCity>
                <code><?= RussiatourismService::getCityCodeById($endNavigation->city_id) ?></code>
            </DestinationCity>
        <?php endif; ?>
        <LeavingCountry>
            <code>RU</code>
        </LeavingCountry>
        <DestinationCountry>
            <code>RU</code>
        </DestinationCountry>
        <Travelers>
            <?php
            foreach ($tourists as $tourist) :
                if (!$tourist) continue;
            ?>
                <Traveler travelerId="<?= $tourist->id ?>">
                    <lastName><?= $tourist->last_name ?></lastName>
                    <firstName><?= $tourist->first_name ?></firstName>
                    <patronymic><?= $tourist->third_name ?></patronymic>
                    <IdentityDocumentType>
                        <code>RU01001</code>
                    </IdentityDocumentType>
                    <identityDocumentSeriesNumber><?= $tourist->serial ?><?= $tourist->number ?></identityDocumentSeriesNumber>
                    <IdentityDocumentIssueCountry>
                        <code>RU</code>
                    </IdentityDocumentIssueCountry>
                    <?php if ($tourist->phone) : ?>
                        <phoneNumber><?= $tourist->phone ?></phoneNumber>
                    <?php endif; ?>
                    <?php if ($tourist->email) : ?>
                        <email><?= $tourist->email ?></email>
                    <?php endif; ?>
                    <birthDate><?= $tourist->birth_date ?></birthDate>
                    <AccommodationTourismServices />
                    <RailTransportationTourismServices />
                    <AirTransportationTourismServices />
                    <TransportationTourismServices>
                        <TransportationTourismService serviceId="<?= $tour->id ?>">
                            <?php if ($tour->title) : ?>
                                <description><?= $tour->title ?></description>
                            <?php endif; ?>
                            <?php if ($startNavigation) : ?>
                                <departurePoint><?= $startNavigation->city->title ?></departurePoint>
                                <departureDate><?= date("Y-m-d", strtotime($departure_dt)) ?></departureDate>
                                <departureTime><?= date("H:i:s", strtotime($departure_dt)) ?></departureTime>
                            <?php endif; ?>
                            <?php if ($endNavigation) : ?>
                                <arrivalPoint><?= $endNavigation->city->title ?></arrivalPoint>
                                <arrivalDate><?= date("Y-m-d", strtotime($arrival_dt)) ?></arrivalDate>
                                <arrivalTime><?= date("H:i:s", strtotime($arrival_dt)) ?></arrivalTime>
                            <?php endif; ?>
                            <routeNumber><?= $tour->id ?></routeNumber>
                            <ticketNumber><?= $order->id ?></ticketNumber>
                            <?php /*
                            <Partner>
                                <code>CODE7105</code>
                            </Partner>
                            */ ?>
                        </TransportationTourismService>
                    </TransportationTourismServices>
                    <TourismServices />
                    <?php /*
                     <TourismServices>
                         <TourismService serviceId="345345">
                             <TourismServiceType>
                                 <code>excursion</code>
                             </TourismServiceType>
                             <description>Экскурс в глубинку столицы</description>
                             <Partner>
                                 <code>CODE7105</code>
                             </Partner>
                         </TourismService>
                     </TourismServices>*/ ?>
                </Traveler>
            <?php endforeach; ?>
        </Travelers>
    </Voucher>
</CreateVoucherRequest>