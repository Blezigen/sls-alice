<?php

namespace integration\models\odata;

class OdataContragent extends OdataBase
{
    public static function tableName()
    {
        return 'Catalog_Контрагенты';
    }

    public static function import($limit = 10, $offset = 0)
    {
        $result = [];

        $model = static::find()
            ->orderBy("ДатаРегистрации desc")
            ->where([
                'DeletionMark' => false,
                ['!=', 'НаименованиеПолное', '']
            ])
            ->limit($limit)
            ->offset($offset)
            ->all();

        foreach ($model as $data) {
            $contractor = static::importData($data);

            $result[] = [
                'contractor' => $contractor,
                'data' => $data
            ];
        }

        return $result;
    }

    protected static function importData($data)
    {
        $model = new static;
        $service = $model->importService();

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
                // "contractor_type_cid" => \common\IConstant::CONTRACTOR_LEGAL_ENTITY
                // "contractor_type_cid" => \common\IConstant::CONTRACTOR_AGENT
            ];
        } else {
            $contractorAttr = [
                "identity_document_id" => $identityDocument->id,
                "contractor_type_cid" => \common\IConstant::CONTRACTOR_PHYSICAL_PERSON
            ];
        }

        $contractor = $service->importContractor($contractorAttr, $data->{"Ref_Key"});

        return $contractor;
    }
}
