<?php

namespace integration\models\odata;

class OdataOrder extends OdataBase
{
    public static function tableName()
    {
        return 'Document__СпутникГермес_СчетНаОплатуТураНаТеплоходе';
    }

    public static function import($limit = 10, $offset = 0)
    {
        $result = [];

        $model = static::find()
            ->with(["Контрагент", "Тур", "Теплоход"])
            ->orderBy("Date desc")
            ->where([
                'DeletionMark' => false,
            ])
            ->limit($limit)
            ->offset($offset)
            ->all();

        foreach ($model as $data) {
            $order = static::importData($data);

            $result[] = [
                'order' => $order,
                'data' => $data
            ];
        }

        return $result;
    }

    protected static function importData($data)
    {
        $model = new static;
        $service = $model->importService();

        $shipAttr = $service->prepareShip($data->{"Теплоход"});
        $ship = $service->importShip($shipAttr, $data->{"Теплоход"}->{"Ref_Key"});

        if (!$ship) {
            return false;
        }

        $tourAttr = $service->prepareTour($data->{"Тур"});
        $tour = $service->importTour($tourAttr, $data->{"Тур"}->{"Ref_Key"});

        if (!$tour) {
            return false;
        }

        $contractor = $model->getContractor($data->{"Контрагент"});

        if (!$contractor) {
            return false;
        }

        $order = $service->importOrder([
            'tour_id' => $tour->id,
            'comment' => $data->{"Number"},
            'contractor_id' => $contractor->id
        ], $data->{"Ref_Key"});

        return $order;
    }

    protected function getContractor($data)
    {
        $service = $this->importService();

        $company = null;
        $identityDocument = null;

        $isCompany = $data->{"ЮридическоеФизическоеЛицо"} == "ЮридическоеЛицо";
        $isIp = strpos($data->{"НаименованиеПолное"}, "ИП ") == 0;

        if ($isCompany || $isIp) {
            $companyAttr = $service->prepareCompany($data);
            $company = $service->importCompany($companyAttr, $data->{"Ref_Key"});
        } else {
            $documentAttr = $service->prepareIdentityDocument($data);
            $identityDocument = $service->importIdentityDocument($documentAttr, $data->{"Ref_Key"});
        }

        if (!$company && !$identityDocument) {
            return;
        }

        if ($company) {
            $contractorAttr = [
                "company_id" => $company->id,
                "contractor_type_cid" => \common\IConstant::CONTRACTOR_AGENT
                // "contractor_type_cid" => \common\IConstant::CONTRACTOR_LEGAL_ENTITY
            ];
        } else {
            $contractorAttr = [
                "identity_document_id" => $identityDocument->id,
                "contractor_type_cid" => \common\IConstant::CONTRACTOR_PHYSICAL_PERSON
            ];
        }

        return $service->importContractor($contractorAttr, $data->{"Ref_Key"});
    }
}
