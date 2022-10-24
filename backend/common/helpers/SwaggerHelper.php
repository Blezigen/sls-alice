<?php

namespace common\helpers;

use api\modules\setting_module\models\TourFilter;
use Carbon\Carbon;
use common\contracts\ISwaggerDoc;
use OpenApi\Annotations\Items;
use OpenApi\Annotations\Parameter;
use OpenApi\Annotations\Property;
use OpenApi\Annotations\Schema;
use yii\db\ActiveRecord;

class SwaggerHelper
{
    public static function headerProperty(
        $headerName,
        $description = '',
        $options = []
    ) {
        $property = [
            'in' => 'header',
            'name' => $headerName,
            'description' => $description,
            'example' => $options['example'] ?? '',
        ];

        if (array_key_exists('enum', $options)) {
            $property['schema'] = new Schema([
                'type' => 'array',
                'items' => new Items([
                    'type' => 'string',
                    'enum' => $options['enum'],
                ]),
            ]);
        }

        return new Parameter($property);
    }

    public static function extraProperty($description = '', $enum = [])
    {
        $property = [
            'in' => 'query',
            'name' => 'extend',
            'description' => $description,
        ];

        $property['schema'] = new Schema([
            'type' => 'array',
            'items' => new Items([
                'type' => 'string',
                'enum' => $enum,
            ]),
        ]);

        return new Parameter($property);
    }

    public static function pathProperty($routeProperty, $description = '')
    {
        $property = [
            'in' => 'path',
            'name' => $routeProperty,
            'description' => $description,
        ];

        return new Parameter($property);
    }

    public static function securityBearerAuth()
    {
        return ['BearerAuth' => []];
    }

    public static function securityAccessToken()
    {
        return ['ApiKey' => []];
    }

    public static function securityOAuth()
    {
        return ['OAuth2' => []];
    }

    public static function extraPropertyFromClass($description, $classname)
    {
        if (in_array(ISwaggerDoc::class, class_implements($classname))) {
            /** @var ISwaggerDoc|ActiveRecord $model */
            $model = $classname::__makeDocumentationEntity();
            if (!$model instanceof ActiveRecord) {
                throw new \Exception('Не является ActiveRecord');
            }
//                $docs = $model->;

            $enum = array_keys($model->extraFields());

            $property = [
                'in' => 'query',
                'name' => 'extend',
                'explode' => false,
                'description' => $description,
            ];

            $property['schema'] = new Schema([
                'type' => 'array',
                'items' => new Items([
                    'type' => 'string',
                    'enum' => $enum,
                ]),
            ]);

            return new Parameter($property);
        }
    }

    public static function queryProperty(
        $description,
        $name
    ) {
        $properties['q'] = new Property([
            'property' => $name,
            'title' => $description,
            'example' => '',
        ]);

        $property['schema'] = new Schema([
            'type' => 'object',
            'properties' => $properties,
        ]);

        return new Parameter($property);
    }

    public static function getProperty(
        $description,
        $name
    ) {
        if (empty($description)) {
            $description = 'Сортировка';
        }

        $property = [
            'in' => 'query',
            'name' => "$name",
            'description' => $description,
        ];

        return new Parameter($property);
    }

    public static function headerPropertyByDate() {
        $property = [
            'in' => 'header',
            'name' => 'X-Datetime',
            'description' => "Позволяет получить данные на дату, если значение пустое, то выводятся актуальные данные.",
            'example' => Carbon::now()->endOfDay()->format('Y-m-d H:i:s'),
        ];

        return new Parameter($property);
    }

    public static function filterPropertyFromClass(
        $description,
        $classname,
        $withQ = false
    ) {
        if (in_array(ISwaggerDoc::class, class_implements($classname))) {
            /** @var ISwaggerDoc|ActiveRecord $model */
            $model = $classname::__makeDocumentationEntity();
            $ignore = $classname::__docAttributeIgnore() ?? [];

            if (!$model instanceof ActiveRecord) {
                throw new \Exception('Не является ActiveRecord');
            }
            $property = [
                'in' => 'query',
                'name' => 'filter',
                'description' => $description,
            ];

            $properties = [];

            foreach ($model->toArray() as $fieldKey => $fieldValue) {
                $attribute = $fieldKey;
                if (in_array($attribute, $ignore)) {
                    continue;
                }

                $properties[$attribute] = new Property([
                    'property' => "filter[$attribute]",
                    'title' => $attribute,
                    'description' => $attribute,
                    'example' => '',
                ]);
            }

            if ($withQ) {
                $properties['q'] = new Property([
                    'property' => 'filter[q]',
                    'title' => 'Filter Query property',
                    'example' => '',
                ]);
            }

            $property['schema'] = new Schema([
                'type' => 'object',
                'properties' => $properties,
            ]);

            return new Parameter($property);
        }
    }

    /**
     * Пример:
     * ```php
     * SwaggerHelper::sortProperty("Сортировка", $this->sorts())
     * ```
     *
     * @return Parameter|null
     */
    public static function sortProperty(string $string, array $sorts)
    {
        if (empty($description)) {
            $description = 'Сортировка';
        }

        $property = [
            'in' => 'query',
            'name' => 'sort',
            'description' => $description . '. Допустимые значения:',
        ];

//        foreach ($sorts as $s => $value) {
//            $property['description'] .= $s . ', -' . $s . ', ';
//        }

        $properties = [];
        if ($sorts) {
            $properties[] = new Property([
                'property' => 'sort',
                'title' => 'sort',
                'description' => $description,
                'example' => '',
            ]);

            $property['schema'] = new Schema([
                'type' => 'object',
                'properties' => $properties,
            ]);

            return new Parameter($property);
        }

        return null;
    }

    /**
     * Пример:
     * @return Parameter|null
     */
    public static function paginatorProperty()
    {
        $properties = [];

        $property = [
            'in' => 'query',
            'name' => 'page',
            'description' => "Выборочное ограничение записей, используется либо `skip/take` либо `page/limit`",
        ];

        $properties[] = new Property([
            'property' => 'page',
            'title' => 'page',
            'example' => '1',
        ]);
        $properties[] = new Property([
            'property' => 'limit',
            'title' => 'limit',
            'example' => '100',
        ]);
        $properties[] = new Property([
            'property' => 'skip',
            'title' => 'skip',
            'example' => null,
        ]);
        $properties[] = new Property([
            'property' => 'take',
            'title' => 'take',
            'example' => null,
        ]);

        $property['schema'] = new Schema([
            'type' => 'object',
            'properties' => $properties,
        ]);

        return new Parameter($property);
    }

    public static function fieldsProperty()
    {
        $property = [
            'in' => 'query',
            'name' => 'fields',
            'explode' => false,
            'description' => "",
        ];

        $property['schema'] = new Schema([
            'type' => 'string',
        ]);

        return new Parameter($property);
    }
}
