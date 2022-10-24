<?php

namespace common\modules\filter\functions;

use common\contracts\models\IQueryFilter;
use common\modules\filter\AbstractFilterMethod;

class QueryFilter extends AbstractFilterMethod
{
    protected static string $keyWord = '';

    public function __construct($string)
    {
        $this->parsedString = $string;
    }

    public function prepare(\yii\db\ActiveQuery $query)
    {
        /** @var IQueryFilter $modelClass */
        $modelClass = $query->modelClass;
        if ($this->getAttribute($query->modelClass) === 'q' and array_key_exists(IQueryFilter::class, class_implements($modelClass))) {
            $condition = $modelClass::singleQueryQuestion($this->parsedString);
            $query->andWhere($condition);
        }
    }

    /**
     * @param $string
     *
     * @return false|static
     *
     * @throws \ReflectionException
     */
    public static function make($string)
    {
        $reflector = new \ReflectionClass(static::class);

        /** @var static $object */
        $object = $reflector->newInstanceArgs([
            $string,
        ]);

        return $object;
    }
}
