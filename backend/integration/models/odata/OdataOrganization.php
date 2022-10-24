<?php

namespace integration\models\odata;

class OdataOrganization extends OdataBase
{
    public static function tableName()
    {
        return 'Catalog_Организации';
    }

    public static function import($limit = 10, $offset = 0)
    {
        $result = [];

        $model = static::find()
            ->where([
                'DeletionMark' => false
            ])
            ->limit($limit)
            ->offset($offset)
            ->all();

        foreach ($model as $data) {
            $organization = static::importData($data);

            $result[] = [
                'organization' => $organization,
                'data' => $data
            ];
        }

        return $result;
    }

    protected static function importData($data)
    {
        $model = new static;
        $service = $model->importService();

        $companyAttr = $service->prepareCompany($data);
        $company = $service->importCompany($companyAttr, $data->{"Ref_Key"});

        return $company;
    }
}
