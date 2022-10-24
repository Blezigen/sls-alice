<?php

namespace doc;

use yii\di\Instance;

class DocumentationHelper
{
    public static function getExampleValue($attribute, $examples = [])
    {
        if (array_key_exists($attribute, $examples)) {
            return $examples[$attribute];
        }
        foreach ($examples as $key => $value) {
            if (is_array($value) && array_key_exists('_type', $value)
                && $value['_type'] === 'regex'
            ) {
                $keyReg = $value['key'];
                $valueResult = $value['value'];

                if (preg_match($keyReg, $attribute, $matches) === 1) {
                    if ($valueResult instanceof \Closure) {
                        return $valueResult($key, $matches);
                    } elseif (!is_array($valueResult) && $valueResult !== null && class_exists($valueResult)) {
                        return DocumentationHelper::ensure($valueResult);
                    } else {
                        return $valueResult;
                    }
                }
            }
        }

        return null;
    }

    public static function ensure($ensureClass)
    {
        if (is_object($ensureClass)) {
            $temp = $ensureClass;
        } elseif (class_exists($ensureClass)) {
            $temp = Instance::ensure($ensureClass);
        } else {
            throw new \Exception("Ensure $ensureClass error!");
        }

        $ignoredAttributes = [];
        $attributeExamples = [];

        if (method_exists($temp, '__docAttributeIgnore')) {
            $ignoredAttributes = $temp->__docAttributeIgnore();
        }
        if (method_exists($temp, '__docAttributeExample')) {
            $attributeExamples = $temp->__docAttributeExample();
        }

        $attributes = [];

        if (method_exists($temp, 'attributes')) {
            $attributes = array_merge($attributes, $temp->attributes());
        }
        if (method_exists($temp, 'fields')) {
            $attributes = array_merge($attributes, $temp->fields());
        }

        foreach ($attributes as $attribute => $value) {
            $field = $attribute;

            if (is_numeric($attribute)) {
                $temp->$value = self::getExampleValue($value, $attributeExamples);
                continue;
            }

            if (is_numeric($field)) {
                $field = $value;
            }

            if (in_array($field, $ignoredAttributes)) {
                continue;
            }

            $temp->$attribute = self::getExampleValue($attribute,
                $attributeExamples);
        }

        return $temp;
    }
}
