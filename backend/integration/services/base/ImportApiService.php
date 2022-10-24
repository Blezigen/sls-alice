<?php

namespace integration\services\base;

use Yii;
use yii\base\Model;
use yii\httpclient\Client;

use integration\models\Integration;
use integration\helpers\Csv2Array;

use common\IConstant;
use common\models\Collection;
use common\models\Order;
use common\components\services\OrderService;
use common\contracts\IOrderService;
use common\models\Tour;
use common\models\Account;

use common\exceptions\ValidationException;
use common\models\OrderPayment;

abstract class ImportApiService extends Model
{
    public string $baseurl;
    public string $basepath = "";
    public string $login;
    public string $pass;
    public string $token;
    public int $shipId = 0; // Промежуточная переменная
    public string $company;
    public int $companyId = 0;

    /****************************************************************/

    protected function _authUser()
    {
        $identity = Account::findOne(['username' => '79000000000']);
        \Yii::$app->user->login($identity);
    }

    public function createCompany($isCreate = true)
    {
        $this->_authUser();

        $data = [
            'title' => $this->company
        ];

        if (!$isCreate) {
            return $data;
        }

        $model = $this->findOrCreate("Company", $data);

        if ($model->id) {
            $this->companyId = $model->id;
        }

        return $model;
    }

    public function importRegion($data, $externalId = null)
    {
        $find = [
            "like", "title", $data["title"]
            // "title" => $data["title"]
        ];

        return $this->findOrCreate("Region", $find, $data, $externalId);
    }

    public function importIdentityDocument($data, $externalId = null)
    {
        $find = $data;

        return $this->findOrCreate("IdentityDocument", $find, $data, $externalId);
    }

    public function importCompany($data, $externalId = null)
    {
        $find = [
            "inn" => $data["inn"]
        ];

        return $this->findOrCreate("Company", $find, $data, $externalId);
    }

    public function importContractor($data, $externalId = null)
    {
        $find = $data;

        return $this->findOrCreate("Contractor", $find, $data, $externalId);
    }

    public function importCity($data, $externalId = null)
    {
        $current = Csv2Array::findCity($data["title"]);

        if (!$current) {
            return false;
        }

        $regionData = [
            "title" => $current["region"]
        ];

        $region = $this->importRegion($regionData);

        if (!$region)
            $region = $this->importRegion(["title" => "Не определен"]);

        $find = [
            "title" => $current["city"],
            "region_id" => $region->id
        ];

        $data["region_id"] = $region->id;
        $data["title"] = $current["city"];

        return $this->findOrCreate("City", $find, $data, $externalId);
    }

    public function importShipNavigation($data, $externalId = null)
    {
        $find = [
            "ship_id" => $data["ship_id"],
            "city_id" => $data["city_id"],
        ];

        if (!empty($data["tour_id"])) {
            $find["tour_id"] = $data["tour_id"];
        }

        return $this->findOrCreate("ShipNavigation", $find, $data, $externalId);
    }

    public function importShip($data, $externalId = null)
    {
        if ($this->companyId)
            $data = array_merge($data, [
                'company_id' => $this->companyId
            ]);

        $find = [
            "title" => $data["title"]
        ];

        $model = $this->findOrCreate("Ship", $find, $data, $externalId);

        if ($model->id) {
            $this->shipId = $model->id;
        }

        return $model;
    }

    public function importClassCabin($data, $externalId = null)
    {
        $find = [
            "ship_id" => $data["ship_id"],
            "title" => $data["title"]
        ];

        return $this->findOrCreate("ShipCabinClass", $find, $data, $externalId);
    }

    public function importCabin($data, $externalId = null)
    {
        return $this->findOrCreate("Cabin", $data, $data, $externalId);
    }

    public function importPrice($data, $externalId = null)
    {
        $find = [
            "tour_id" => $data["tour_id"],
            "ship_cabin_class_id" => $data["ship_cabin_class_id"]
        ];

        return $this->findOrCreate("TourCabinPrice", $find, $data, $externalId);
    }

    public function importTour($data, $externalId = null)
    {
        $model = Tour::find()
            ->withoutTrashed()
            ->where([
                "ship_id" => $data["ship_id"],
                "departure_dt" => $data["departure_dt"],
                "arrival_dt" => $data["arrival_dt"],
            ])
            ->one();


        if (!$model) {
            $model = new Tour();
        }

        $model->attributes = $data;

        if ($model->save()) {
            Integration::findOrCreate($model->id, $externalId, "Tour", $this->company);
        }

        return $model;
    }

    public function importPayment($data, $externalId = null)
    {
        if ($externalId) {
            $integration = Integration::find()
                ->where([
                    "external_cid" => $externalId,
                    "service" => $this->company,
                    "type" => "OrderPayment"
                ])
                ->one();

            if ($integration)
                return $integration->internal_cid;
        }

        $model = OrderPayment::find()
            ->where([
                "order_id" => $data["order_id"],
                "payment_amount" => $data["payment_amount"],
                "payment_number" => $data["payment_number"],
            ])
            ->where($data)
            ->one();

        if (!$model) {
            $status = Collection::find()
                ->where(['slug' => IConstant::PAYMENT_STATUS_NOT_PAYED])
                ->one();

            $model = new OrderPayment();

            $model->attributes = $data;
            $model->status_cid = $status->id;
            $model->payment_type = $this->company;

            if (!$model->validate()) {
                throw new ValidationException($model->errors);
            }

            $model->save();
        }

        if ($model->id) {
            Integration::findOrCreate($model->id, $externalId, "OrderPayment", $this->company);
        }

        return $model->id;
    }

    public function importOrder($data, $externalId = null)
    {
        if ($externalId) {
            $integration = Integration::find()
                ->where([
                    "external_cid" => $externalId,
                    "service" => $this->company,
                    "type" => "Order"
                ])
                ->one();

            if ($integration)
                return $integration->internal_cid;
        }


        $model = Order::find()
            ->withoutTrashed()
            ->where($data)
            ->one();

        if (!$model) {
            $collection = Collection::find()
                ->slug(IConstant::ORDER_TYPE_GENERAL)
                ->one();

            $model = new Order();
            $model->attributes = $data;
            $model->order_type_cid = $collection->id;

            if (!$model->validate()) {
                throw new ValidationException($model->errors);
            }

            $model->save();
        }

        if ($model->id) {
            Integration::findOrCreate($model->id, $externalId, "Order", $this->company);
        }

        return $model->id;
    }

    public function getOrderByID($id)
    {
        return Order::find()
            ->byId($id)
            ->one();
    }

    public function getOrderByExternalID($externalId)
    {
        $integration = Integration::find()
            ->where([
                "external_cid" => $externalId,
                "service" => $this->company,
                "type" => "Order"
            ])
            ->one();

        if ($integration)
            return Order::find()
                ->byId($integration->internal_cid)
                ->one();

        return null;
    }

    public function getPaymentByExternalID($externalId)
    {
        $integration = Integration::find()
            ->where([
                "external_cid" => $externalId,
                "service" => $this->company,
                "type" => "OrderPayment"
            ])
            ->one();

        if ($integration)
            return OrderPayment::find()
                ->byId($integration->internal_cid)
                ->one();

        return null;
    }

    /****************************************************************/

    protected function findOrCreate($classname, $find, $data = null, $externalId = null)
    {
        if (!$data) {
            $data = $find;
        }

        if ($externalId) {
            $integration = Integration::find()
                ->where([
                    "external_cid" => $externalId,
                    "service" => $this->company,
                    "type" => $classname
                ])
                ->one();

            if ($integration && $integration->internal_cid) {
                $find = ["id" => $integration->internal_cid];
            }
        }

        $classnameFull = "\\common\\models\\{$classname}";

        $query = $classnameFull::find()
            // ->withoutTrashed()
            // ->byVersion()
            ->andWhere($find);

        $model = $query->one();

        if (!$model) {
            $model = new $classnameFull();
            $model->attributes = $data;
            $model->save();
        } else {
            try {
                $model->attributes = $data;
                $model->save();
            } catch (\Throwable $th) {
                //throw $th;
            }
        }

        if ($model->errors) {
            var_dump($classnameFull);
            var_dump($data);
            var_dump($model->errors);
            die();
        }

        Integration::findOrCreate($model->id, $externalId, $classname, $this->company);

        return $model;
    }

    protected function cache()
    {
        return \Yii::$app->cache;
    }

    public function get($path, $params = [], $headers = [])
    {
        $path = "{$this->basepath}/{$path}";

        $cacheKey = "{$this->baseurl}_{$path}_" . md5(json_encode($params));
        $params = array_merge($params, $this->getBasicParams());
        $headers = array_merge($headers, $this->getAuthHeaders());

        $response = $this->cache()->getOrSet($cacheKey, function () use ($path, $params, $headers) {
            return $this->client()->get($path, $params, $headers)->send();
        }, 3600);

        if ($response->isOk) {
            $content = json_decode($response->content, true);
            return (isset($content['result'])) ? $content['result'] : $content;
        } else {
            var_dump($response);
            die();
        }

        return false;
    }

    public function post($path, $data = null, $headers = [])
    {
        $path = "{$this->basepath}/{$path}";
        $headers = array_merge($headers, $this->getAuthHeaders());

        $response = $this->client()->post($path, $data, $headers)->send();

        if ($response->isOk) {
            $content = json_decode($response->content, true);
            return $content['result'];
        }

        return false;
    }

    public function client()
    {
        return new Client(['baseUrl' => $this->baseurl]);
    }

    protected function getAuthHeaders()
    {
        if ($token = $this->getAccessToken())
            return ['Authorization' => 'Bearer ' . $token];

        if ($this->token)
            return ['x-api-key' => $this->token];

        return [];
    }

    abstract function getAccessToken();
    abstract function getBasicParams();
}
