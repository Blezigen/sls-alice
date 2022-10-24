<?php

namespace integration\services;

use yii\base\Model;
use yii\httpclient\Client;

/*
    OData.  https://accutest.sputnik-germes.ru:8443/g1tst/odata/standard.odata/   
    Login: odataadmin 
*/

class OdataService extends Model
{
    private string $baseurl;
    private string $login;
    private string $pass;

    public function init()
    {
        parent::init();

        $params = \Yii::$app->params['odata'];

        $this->baseurl = $params['baseurl'];
        $this->login = $params['login'];
        $this->pass = $params['pass'];
    }

    public function get($method, $params = [])
    {
        $params['$format'] = 'json';
        $params['$inlinecount'] = 'allpages';

        if (!isset($params['$top'])) {
            $params['$top'] = '50';
        }

        $query = [];

        foreach ($params as $key => $value) {
            $value = str_replace("+", "%20", urlencode($value));

            $query[] = "{$key}={$value}";
        }

        $path = $method . '?' . implode("&", $query);
        $cacheKey = md5($path);

        return $this->cache()->getOrSet($cacheKey, function () use ($path) {
            return $this->_request($path);
        }, 600);
    }

    public function post($method, $params)
    {
        $path = urlencode($method) . '?$format=json';

        return $this->_request($path, "POST", $params); // return $response->Ref_Key;
    }

    public function patch($method, $params)
    {
        $path = urlencode($method) . '?$format=json';

        return $this->_request($path, "PATCH", $params); // return $response->Ref_Key;
    }

    public function delete($method)
    {
        $path = urlencode($method) . '?$format=json';

        return $this->_request($path, "DELETE");
    }

    private function _request($path, $method = "GET", $data = null)
    {
        $client = new Client([
            'responseConfig' => [
                'format' => Client::FORMAT_JSON
            ],
            'requestConfig' => [
                'format' => Client::FORMAT_JSON
            ],
        ]);

        $auth64 = base64_encode("{$this->login}:{$this->pass}");
        $url = $this->baseurl . $path;

        $response = $client->createRequest()
            ->addHeaders([
                'Authorization' => 'Basic ' . $auth64,
                'Content-Type' => 'application/json',
                'cache-control' => 'no-cache'
            ])
            ->setMethod($method)
            ->setUrl($url)
            ->setData($data)
            ->send();

        if ($response->isOk) {
            return json_decode($response->content);
        } else {
            var_dump($response);
            die();
        }

        return false;
    }

    protected function cache()
    {
        return \Yii::$app->cache;
    }
}
