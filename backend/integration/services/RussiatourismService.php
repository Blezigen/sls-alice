<?php

namespace integration\services;

use integration\helpers\ParserSOAP;
use yii\helpers\FileHelper;

use common\models\City;
use common\models\Order;
use common\models\IdentityDocument;
use integration\services\base\ImportApiService;
use yii\web\HttpException;
use integration\models\Integration;

/*
    https://eisep.ru/

    https://www.cryptopro.ru/sites/default/files/images/faq/stunnel_client__windows_10_i_server_mac_os_x_sg.pdf

    /opt/cprocsp/sbin/stunnel_thread /Users/redboxstudio/Sites/git/micro-germes/germes.river.backend/backend/integration/gost/stunnel.conf 
*/

// class RussiatourismService extends \yii\base\Model
class RussiatourismService extends ImportApiService
{
    public string $registryID;

    private string $pathXsd;
    private string $pathXml;

    private string $wsdlUrl;
    private string $baseUrl;
    private string $mnemonic;
    private string $humanReadableName;

    private string $templatePath = "@integration/services/views";

    public function init()
    {
        parent::init();

        $params = \Yii::$app->params['russiatourism'];

        $this->wsdlUrl = $params['wsdlUrl'];
        $this->baseUrl = $params['baseUrl'];
        $this->registryID = $params['registryID'];
        $this->mnemonic = $params['senderMnemonic'];
        $this->humanReadableName = $params['senderHumanReadableName'];

        $basePath = \Yii::getAlias('@integration-cdn');

        $this->pathXsd = "{$basePath}/xsd/russiatourism";
        $this->pathXml = "{$basePath}/xml/russiatourism";

        $this->company = "EISEP";
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

    public function importCities()
    {
        ini_set('memory_limit', '512M');

        $content = file_get_contents("xml/CITY.xml");
        $data = ParserSOAP::toObject($content);

        foreach ($data->rec as $item) {
            if ($item->codeCountry != "RU") {
                continue;
            }

            $this->importCity([
                "title" => $item->nameRU
            ], $item->code);
        }

        return [
            "count" => count($data->rec)
        ];
    }

    public function CreateVoucherByOrderID($orderId)
    {
        // return $this->getResponse("c0a2eba0-463f-11ed-80a3-f543a61bd473");

        $number = $this->CreateVoucherNumber();

        if (!$number) {
            return false;
        }

        $params = $this->prepareDataByOrderID($orderId, $number);
        $params["number"] = $number;

        return $this->CreateVoucher($params);
    }

    public function CreateVoucher($params)
    {
        return $this->sendRequest("CreateVoucher", $params, "CreateVoucher");
    }

    public function CreateVoucherNumber()
    {
        $sleepSec = 2;
        $response = $this->sendRequest("CreateVoucherNumber");

        if (isset($response->RequestId)) {
            sleep($sleepSec);

            for ($i = 0; $i < 10; $i++) {
                $resp = $this->getResponse($response->RequestId);

                try {
                    return $resp->MessagePrimaryContent->CreateVoucherNumberResponse->voucherNumber;
                } catch (\Throwable $th) {
                    sleep($sleepSec);
                    continue;
                }
            }
        }

        return false;
    }


    private function validateFile($content, $xsd)
    {
        $xml = new \DOMDocument();
        $xml->loadXML($content);

        if (!$xml->schemaValidate($xsd)) {
            print '<b>DOMDocument::schemaValidate() Generated Errors!</b>';
            return false;
        }

        return true;
    }

    private function getResponse($id)
    {
        $controller = \Yii::$app->controller;

        $soapContent = $controller->renderPartial("{$this->templatePath}/GetResponseBase", [
            "requestId" => $id,
            "mnemonic" => $this->mnemonic,
            "humanReadableName" => $this->humanReadableName,
        ]);

        $result = $this->soapCurl('urn:GetResponse', $soapContent);
        $response = $result->Body->GetResponseResponse->GetResponseResponse;

        try {
            return $response->ResponseMessage->Response->SenderProvidedResponseData;
        } catch (\Throwable $th) {
            // throw $th;
            throw new HttpException(206, json_encode($result->Body->GetResponseResponse));
        }

        return false;
    }

    private function getResponseResult($id, $zipStr)
    {
        $dir = \Yii::getAlias('@integration-cdn') . "/russiatourism/{$id}";
        $file = "{$dir}/response.zip";

        FileHelper::createDirectory($dir);

        $zipfile = fopen($file, "w") or die("Unable to open file!");
        fwrite($zipfile, base64_decode($zipStr));
        fclose($zipfile);

        $zip = new \ZipArchive();

        if ($zip->open($file) === true) {
            $zip->extractTo($dir);
            $zip->close();

            $files = FileHelper::findFiles($dir, ["only" => ["*.xml"]]);

            if ($files) {
                return ParserSOAP::toArray(file_get_contents($files[0]));
            }
        }

        return false;
    }

    private function sendRequest($request, $params = [], $template = "SendRequestSimple")
    {
        $controller = \Yii::$app->controller;
        $params["request"] = $request;

        $content = $controller->renderPartial("{$this->templatePath}/{$template}", $params);

        $soapContent = $controller->renderPartial("{$this->templatePath}/SendRequestBase", [
            "content" => $content,
            "mnemonic" => $this->mnemonic,
            "humanReadableName" => $this->humanReadableName,
        ]);

        if ($template != "SendRequestSimple") {
            $xsd_file = "{$this->pathXsd}/{$request}/{$request}Request.xsd";

            if (!$this->validateFile($content, $xsd_file)) {
                return false;
            }
        }

        $response = $this->soapCurl("urn:SendRequest", $soapContent);

        try {
            return $response->Body->SendRequestResponse->SendRequestResponse->MessageMetadata;
        } catch (\Throwable $th) {
            throw $th;
        }

        return false;
    }

    private function soapCurl($soapAction, $soapContent)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type' => 'text/xml;charset=UTF-8',
            'SOAPAction' => $soapAction,
        ]);
        // curl_setopt($ch, CURLOPT_POSTFIELDS, '@request_partners.xml');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $soapContent);

        $response = curl_exec($ch);
        curl_close($ch);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            throw new HttpException(501, $error_msg);
        }

        return ParserSOAP::toObject($response);
    }

    private static function toXml($array, $keyParent = null)
    {
        $xml = '';
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if (strpos($key, '@') === 0) {
                    continue;
                }
                if (is_numeric($key)) {
                    $key = "element";
                }
                if (is_array($value)) {
                    if ($keyParent && $key == 'element') {
                        $key = substr($keyParent, 0, -1);
                    }

                    $keyAttrs = [];
                    if (isset($value['@attrs'])) {
                        foreach ($value['@attrs'] as $kAttr => $vAttr) {
                            $keyAttrs[] = $kAttr . '="' . $vAttr . '"';
                        }
                    }
                    $attrStr = implode(" ", $keyAttrs);

                    $xml .= "<$key $attrStr>" . static::toXml($value, $key) . "</$key>";
                } elseif (strlen(trim($value)) === 0) {
                    $xml .= "<$key/>";
                } else {
                    $xml .= "<$key>" . htmlspecialchars($value) . "</$key>";
                }
            }
        }
        return $xml;
    }

    public static function encodeXml($array, $rootTag)
    {
        $xml = static::toXml($array);
        $header = '<?xml version="1.0" encoding="utf-8"?>';
        $rootTagAttr = 'xmlns="urn://artefacts-russiatourism-ru/services/message-exchange/types/' . $rootTag . '" xsi:schemaLocation="urn://artefacts-russiatourism-ru/services/message-exchange/types/' . $rootTag . ' schema.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"';
        return "$header<{$rootTag}Request {$rootTagAttr}>{$xml}</{$rootTag}Request>";
    }

    public static function getCityCodeById($id)
    {
        $model = Integration::find()
            ->where([
                'internal_cid' => $id,
                'service' => "EISEP",
                "type" => "City"
            ])
            ->one();

        return $model ? $model->external_cid : "RUMOW";
    }

    protected function prepareDataByOrderID($orderID, $number = null)
    {
        $order = Order::find()
            ->byId($orderID)
            ->one();

        if (!$order) {
            throw new HttpException(404, 'Заказ не найден');
        }

        if ($number) {
            Integration::findOrCreate($orderID, $number, "Order", $this->company);
        }

        $tourists = []; // туристы
        $buyer = null; // покупатель

        $documentId = $order->contractor->identity_document_id;

        $document = IdentityDocument::find()
            ->byId($documentId)
            ->one();

        $buyer = $document;
        $cabins = $order->orderCabins;

        foreach ($cabins as $cabin) {
            $places = $cabin->orderPlaces;

            foreach ($places as $place) {
                if ($place->identity_document_id) {
                    $documentId = $place->identity_document_id;

                    if (!$documentId) {
                        continue;
                    }

                    $document = IdentityDocument::find()
                        ->byId($documentId)
                        ->one();

                    // if (!$document) {
                    //     throw new HttpException(404, 'Информация о туристе отсутствует');
                    // }

                    $tourists[] = $document;
                }
            }
        }

        return [
            'buyer' => $buyer,
            'tourists' => $tourists,
            'order' => $order,
            'tour' => $order->tour,
        ];
    }
}
