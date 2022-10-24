<?php

namespace common\modules\filter;

use common\contracts\models\IAttributeConvertFilter;
use common\exceptions\ValidationException;

abstract class AbstractFilterMethod
{
    protected static string $keyWord;

    public static function keyWord()
    {
        return static::$keyWord;
    }

    /**
     * @var mixed
     */
    protected $attribute;

    public $parsedString = null;

    protected static function searchPattern($keyWord)
    {
        return "/{$keyWord}(?:\((?<params>.*)\))?/";
    }

    /**
     * @param  mixed  $attribute
     */
    public function setAttribute($attribute): void
    {
        $this->attribute = $attribute;
    }

    protected static function canParseString($string, &$matches)
    {
        return preg_match(static::searchPattern(static::$keyWord), $string,
            $matches);
    }

    protected static function convertParamsToConstructorParameters($params)
    {
        return $params;
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
        if (!self::canParseString($string, $matches)) {
            return false;
        }

        $params = explode(',', $matches['params'] ?? '');

        $reflector = new \ReflectionClass(static::class);

        foreach ($params as $key => $param) {
            $params[$key] = static::castedParam($param);
        }

        $params = static::convertParamsToConstructorParameters($params);

        $constructor = $reflector->getConstructor();
        if ($constructor) {
            $paramsCount = count($constructor->getParameters());
            $paramsCountExist = count($params ?? []);

            if ($paramsCount < $paramsCountExist
                || $paramsCount > $paramsCountExist
            ) {
                throw new ValidationException(['params' => [static::$keyWord . ' требует определенное кол-во параметров: ' . $paramsCount]]);
            }
        } else {
            $params = [];
        }
        /** @var static $object */
        $object = $reflector->newInstanceArgs($params);
        $object->parsedString = $string;

        return $object;
    }

    /**
     * @return array|mixed
     */
    protected static function castedParam($param)
    {
        if (preg_match("/(?:(?<caster>DATE|INT|FLOAT)\((?<p1>[^)]+)\)$)/",
                $param, $matches) !== 0
        ) {
            $caster = $matches['caster'];
            $p1 = $matches['p1'];
            switch ($caster) {
                case 'DATE':
                    return Caster::castToStringDate($p1);
                case 'INT':
                    return Caster::castToInteger($p1);
                case 'FLOAT':
                    return Caster::castToFloat($p1);
            }
        }

        return $param;
    }

    public function getAttribute($modelClass)
    {
        if (is_subclass_of($modelClass, IAttributeConvertFilter::class)) {
            return $modelClass::filterAttributeConvert($this->attribute);
        }

        return $this->attribute;
    }

//    abstract public function convertToActiveRecordWhereCause($attribute);

    abstract public function prepare(\yii\db\ActiveQuery $query);
}
