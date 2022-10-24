<?php

namespace integration\models\odata;

use yii\base\Model;
use integration\services\OdataService;
use integration\services\OdataImportService;

class OdataBase extends Model
{
    public $_orderBy;
    public $_limit;
    public $_offset;
    public $_tableName;
    public $_where;
    public $_with;

    public static function tableName()
    {
    }

    public static function find()
    {
        $model = new self;
        $model->_tableName = static::tableName();

        return $model;
    }

    public function limit($limit)
    {
        $this->_limit = $limit;

        return $this;
    }

    public function offset($offset)
    {
        $this->_offset = $offset;

        return $this;
    }

    public function where($where)
    {
        $this->_where = $where;

        return $this;
    }

    public function with($with)
    {
        $this->_with = $with;

        return $this;
    }

    public function andWhere($where)
    {
        if ($this->_where)
            $this->_where = array_merge($this->_where, $where);
        else
            $this->_where = $where;


        return $this;
    }

    public function orderBy($orderBy)
    {
        if (!is_array($orderBy))
            $this->_orderBy = $orderBy;

        return $this;
    }

    public function all()
    {
        $params = [];
        $result = [];
        $filter = [];

        if ($this->_orderBy) {
            $params['$orderby'] = $this->_orderBy;
        }

        if ($this->_limit) {
            $params['$top'] = $this->_limit;
        }

        // https://infostart.ru/1c/articles/1570140/
        $logicReplacer = [
            '=' => 'eq',
            '!=' => 'ne',
            '>' => 'gt',
            '>=' => 'ge',
            '<' => 'lt',
            '<=' => 'le',
        ];

        if ($this->_where && is_array($this->_where)) {
            foreach ($this->_where as $key => $value) {
                if (is_array($value) && is_int($key)) {
                    if (count($value) == 3) {
                        list($logic, $fieldName, $fieldValue) = $value;

                        if (isset($logicReplacer[$logic])) {
                            $logic = $logicReplacer[$logic];
                        }

                        if ($logic == "in" && is_array($fieldValue)) {
                            $temp = [];
                            foreach ($fieldValue as $fVal) {
                                $temp[] = "$fieldName eq '$fVal'";
                            }
                            $filter[] = "(" . implode(" or ", $temp) . ")";
                            continue;
                        }

                        // $fieldValue = json_encode($fieldValue);
                        if (is_bool($fieldValue)) {
                            $fieldValue = json_encode($fieldValue);
                        }

                        if (is_string($fieldValue)) {
                            $fieldValue = "'{$fieldValue}'";
                        }

                        if (!$fieldValue) {
                            $fieldValue = "''";
                        }

                        $filter[] = "{$fieldName} {$logic} {$fieldValue}";
                    }
                } elseif (is_string($key)) {
                    if (is_string($value)) {
                        $keyArr = explode(".", $key);

                        if (count($keyArr) == 2 && count(explode("-", $value)) == 5) {
                            $key = $keyArr[1];
                            $value = "cast(guid'{$value}', '{$keyArr[0]}')";
                        } else {
                            $value = "'{$value}'";
                        }
                    }

                    if (is_bool($value)) {
                        $value = json_encode($value);
                    }

                    $filter[] = "{$key} eq {$value}";
                }
            }
        }

        if ($filter) {
            $params['$filter'] = implode(" and ", $filter);
        }

        $response = $this->service()->get($this->_tableName, $params);

        if (isset($response->value)) {
            foreach ($response->value as $value) {
                $result[] = $this->responseOneFormat($value);
            }
        }

        return $result;
    }

    public function one()
    {
        $params = ['$top' => 1];
        $table = $this->_tableName;

        if (!empty($this->_where['Ref_Key'])) {
            $table .= "(guid'" . $this->_where['Ref_Key'] . "')";
        }

        $response = $this->service()->get($table, $params);

        // !!! fix !!!
        // $this->attributes = $response;

        return $this->responseOneFormat($response);
    }

    protected function responseOneFormat($response)
    {
        unset($response->{'odata.metadata'});

        if ($this->_with) {
            $with = is_array($this->_with) ? $this->_with : [$this->_with];

            foreach ($with as $value) {
                $key = $value . '@navigationLinkUrl';

                if (!empty($response->$key)) {
                    try {
                        $response->{"$value"} = $this->service()->get($response->$key);
                        // unset($response->$key);
                        unset($response->{"$value"}->{'odata.metadata'});
                    } catch (\Throwable $th) {
                        $response->{"$value"} = false;
                    }
                }
            }
        }

        return $response;
    }

    protected function service()
    {
        return new OdataService();
    }

    protected function importService()
    {
        return new OdataImportService();
    }
}
