<?php

namespace common;

use Carbon\Carbon;
use common\contracts\ISwaggerDoc;
use doc\DocumentationHelper;
use OpenApi\Annotations\OpenApi;
use OpenApi\Annotations\Property;
use OpenApi\Annotations\Schema;
use yii\base\Model;
use yii\helpers\Inflector;
use yii\web\UploadedFile;

abstract class BaseModel extends Model implements ISwaggerDoc
{
    public static function __docAttributeExample()
    {
        return [
            'id'         => random_int(1, 100),
            'updated_at' => Carbon::now()
                ->format(Carbon::DEFAULT_TO_STRING_FORMAT),
            'created_at' => Carbon::now()
                ->format(Carbon::DEFAULT_TO_STRING_FORMAT),
        ];
    }

    public static function __docAttributeIgnore()
    {
        return [];
    }

    public static function __makeDocumentationEntity()
    {
        return DocumentationHelper::ensure(static::class);
    }

    public function __set($name, $value)
    {
        $setter = Inflector::variablize("set_{$name}_attribute");
        if (method_exists($this, $setter)) {
            $this->$setter($value);

            return;
        } else {
            parent::__set($name, $value);
        }
    }

    public function __get($name)
    {
        $getter = Inflector::variablize("get_{$name}_attribute");
        if (method_exists($this, $getter)) {
            return $this->$getter();
        } else {
            return parent::__get($name);
        }
    }

    public function __docs(OpenApi $openApi)
    {
        $reflect = new \ReflectionClass($this);
        $schema = new Schema([
            'schema'     => $reflect->getShortName(),
            'title'      => $reflect->getShortName(),
            'properties' => [],
        ]);

        $docEntity = null;
        if (method_exists($this, '__makeDocumentationEntity')) {
            $docEntity = $this->__makeDocumentationEntity();
        }
        $ignore = [];
        if (method_exists($this, '__docAttributeIgnore')) {
            $ignore = $this->__docAttributeIgnore();
        }

        foreach ($docEntity->attributes as $key => $field) {
            if (in_array($key, $ignore)) {
                continue;
            }
            $propertyData = [
                'property' => $key,
                'title'    => $docEntity->getAttributeLabel($key),
                'type'     => 'string',
                'example'  => $field,
            ];

            if ($field instanceof UploadedFile) {
                $propertyData['type'] = 'file';
            } elseif (is_object($field)) {
                $propertyData['type'] = 'object';
            } elseif (is_array($field)) {
                $propertyData['type'] = 'array';
            } elseif (is_numeric($field)) {
                $propertyData['type'] = 'integer';
            }

            $prop = new Property($propertyData);
            $schema->properties[] = $prop;
        }

        return $schema;
    }
}
