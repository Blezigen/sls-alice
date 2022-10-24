<?php

namespace integration\helpers;

class Array2Xml
{
    private static function toXml($array)
    {
        $xml = '';
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if (is_numeric($key)) {
                    $key = "element";
                }
                if (is_array($value)) {
                    $xml .= "<$key>" . static::toXml($value) . "</$key>";
                } elseif (strlen(trim($value)) === 0) {
                    $xml .= "<$key/>";
                } else {
                    $xml .= "<$key>" . htmlspecialchars($value) . "</$key>";
                }
            }
        }
        return $xml;
    }

    public static function encodeXml($array, $rootTag = 'root')
    {
        $xml = static::toXml($array);
        return "<$rootTag>$xml</$rootTag>";
    }

    public static function createNodeXml($xml, $array)
    {
        foreach ($array as $item) {

            if (!is_array($item))
                continue;

            if (!isset($item["@tag"])) {
                die();
            }

            $childValue = isset($item["@text"]) ? $item["@text"] : null;
            $xmlChild = $xml->addChild($item["@tag"], $childValue);

            if (isset($item["@attr"]))
                foreach ($item["@attr"] as $key => $value) {
                    $attrK = explode(":", $key);

                    if (!empty($attrK[1])) {
                        // $xmlChild->addAttribute($key, $value, $attrK[0]);
                        $xmlChild->addAttribute($key, $value, "http://www.w3.org/2001/XMLSchema-instance");
                    } else {
                        $xmlChild->addAttribute($key, $value);
                    }
                }

            if (isset($item["@items"])) {
                foreach ($item["@items"] as $itemItem) {
                    $xmlChild = self::createNodeXml($xmlChild, [$itemItem]);
                }
            }
        }

        return $xml;
    }

    public static function createXml($array, $rootArray)
    {
        $rootTag = $rootArray['@tag'];
        $rootAttr = $rootArray['@attr'];

        $attrs = [];
        foreach ($rootAttr as $key => $value) {
            $attrs[] = "{$key}=\"{$value}\"";
        }
        $attrsStr = implode(" ", $attrs);

        $xml = new \SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?><{$rootTag} {$attrsStr}></{$rootTag}>");
        $xml = self::createNodeXml($xml, $array);

        return $xml->asXML();
    }
}
