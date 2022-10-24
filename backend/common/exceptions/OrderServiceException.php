<?php

namespace common\exceptions;

use Redbox\JsonRpc\Contracts\IRpcException;
use yii\web\HttpException;

class OrderServiceException extends HttpException implements IRpcException
{
    /**
     * @var array
     */
    private array $_data = [];

    public function __construct($message = null, $code = -400, $data = [])
    {
        $this->_data = $data;
        parent::__construct(400, \Yii::t("app", $message, $this->_data), $code, null);
    }

    public function fields()
    {
        return [];
    }

    public function extraFields()
    {
        return [];
    }

    public function toArray(
        array $fields = [],
        array $expand = [],
        $recursive = true
    ) {
        return [
            "code" => $this->getCode(),
            "message" => $this->getMessage(),
            "data" => $this->getData()
        ];
    }

    public function getData()
    {
        return $this->_data ?? [];
    }
}