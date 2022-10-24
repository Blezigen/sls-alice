<?php

namespace common\modules\filter;

use common\exceptions\ValidationException;
use common\modules\filter\functions\QueryFilter;
use yii\base\Model;
use yii\web\Request;

class FilterQueryParser extends Model
{
    public string $requestFilterParam = 'filter';
    public array $requestFilters = [];

    public $filterClasses = [];

    public $_result = null;

    public function init()
    {
        parent::init();

        if (\Yii::$app->request instanceof Request) {
            $this->requestFilters
                = \Yii::$app->request->get($this->requestFilterParam, []);
        }
    }

    public function can($attribute)
    {
        return \Yii::$app->user->enforceYii('filter', '*', $attribute);
    }

    public function process()
    {
        if ($this->_result) {
            return $this->_result;
        }

        $factory = (new FilterMethodFactory($this->filterClasses));

        foreach ($this->requestFilters as $attribute => $filterString) {
            if (!$this->can($attribute)) {
                \Yii::debug("Фильтрация остановлена. Не найдено правило для filter[$attribute]");
                $this->addError($this->requestFilterParam, "Нет разрешающего правила для атрибута `$attribute` на использование данного фильтра!");
                continue;
            }

            if ($attribute === 'q') {
                $filter = QueryFilter::make($filterString);
            } else {
                $filter = $factory->make($filterString);
            }

            if ($filter) {
                $filter->setAttribute($attribute);
                $this->_result["$this->requestFilterParam[$attribute]"] = $filter;
            }
        }

        if ($this->hasErrors()) {
            throw new ValidationException($this->errors);
        }

        return $this->_result;
    }

    public function getFilterByKey($key)
    {
        $data = $this->process();

        return $data[$key];
    }

    /**
     * @param $modelClassName
     *
     * @return AbstractFilterMethod[]
     *
     * @throws ValidationException
     */
    public function handle($modelClassName = null)
    {
        return $this->process();
    }

    public function hasFilter(string $string)
    {
        return array_key_exists($string, $this->requestFilters);
    }
}
