<?php

namespace integration\helpers;

class ParserSOAP
{
    public static function toObject($response)
    {
        return self::toArray($response, false);
    }

    public static function toArray($response, $isArray = true)
    {
        $clean_xml = str_ireplace(['soap-env:', 'soap:', 'ns1:', 'ns2:', 'ns3:'], '', $response);
        $xml = simplexml_load_string($clean_xml);

        $json = json_encode($xml);

        return json_decode($json, $isArray);
    }
}
