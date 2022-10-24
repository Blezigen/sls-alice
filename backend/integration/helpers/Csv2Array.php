<?php

namespace integration\helpers;

class Csv2Array
{
    public static function convert($filename = '', $delimiter = ',')
    {
        if (!file_exists($filename) || !is_readable($filename))
            return FALSE;

        $header = NULL;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== FALSE) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                if (!$header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }
        return $data;
    }

    public static function findCity($title = '')
    {
        if (!$title) {
            return false;
        }

        $replacerEnd = [
            ' г' => ''
        ];

        foreach ($replacerEnd as $key => $value) {
            $find = strpos($title, $key);

            if ($find && $find == strlen($title) - strlen($key)) {
                $title = str_replace($key, $value, $title);
            }
        }

        $file = \Yii::getAlias('@integration-cdn') . "/city.csv";
        $cities = \integration\helpers\Csv2Array::convert($file);
        $current = null;

        foreach ($cities as $city) {
            if ($city["city"] && $city["city"] == $title) {
                $current = $city;
                break;
            }

            if (!$city["city"] && $city["region_type"] == "г" && $city["region"] == $title) {
                $current = $city;
                $current["city"] = $city["region"];
                break;
            }
        }

        return $current;
    }
}
