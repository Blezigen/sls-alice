<?php

namespace common;

class Timings
{
    private static array $data = [];

    public static function check($name)
    {
        if (array_key_exists($name, self::$data)) {
            self::$data[$name]['et'] = microtime(true);
        } else {
            self::$data[$name] = [
            'st' => microtime(true),
            'et' => microtime(true),
        ];
        }
    }

    public static function getData()
    {
        $temp = [];
        foreach (self::$data as $item => $value) {
            $res = $value['et'] - $value['st'];
            $temp[] = "$item;dur=" . ($res * 1000);
        }

        return implode(',', $temp);
    }
}
