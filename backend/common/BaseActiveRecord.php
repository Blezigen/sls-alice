<?php

namespace common;

use Carbon\Carbon;
use common\behaviors\BindingProcedureBehavior;
use common\behaviors\DatetimeBehavior;
use common\behaviors\query\HistoryBehaviors;
use common\behaviors\SoftDeleteBehavior;
use common\contracts\IHistoryObject;
use common\contracts\IModelSoftDelete;
use common\contracts\ISwaggerDoc;
use common\contracts\models\IProtectLoopEntity;
use common\events\CanReadAttributesEvent;
use common\exceptions\LoopProtectException;
use common\exceptions\ValidationException;
use common\modules\metaInfo\MetadataService;
use doc\DocumentationHelper;
use Faker\Provider\Uuid;
use OpenApi\Annotations\OpenApi;
use OpenApi\Annotations\Property;
use OpenApi\Annotations\Schema;
use yii\db\ActiveRecord;
use yii\helpers\Inflector;
use yii\web\UploadedFile;

abstract class BaseActiveRecord extends \yii\db\ActiveRecord implements ISwaggerDoc
{
    public const BINDING_DELETE_INTERNAL = 'bindingDeleteInternal';
    public const BINDING_INSERT_INTERNAL = 'bindingInsertInternal';
    public const BINDING_UPDATE_INTERNAL = 'bindingUpdateInternal';

    public $_meta = [];
    public $advanceParams = [];

    /**
     * @var array|null old attribute values indexed by attribute names.
     * This is `null` if the record [[isNewRecord|is new]].
     */
    private array|null $_oldAttributes;

    public static function firstOrCreate($condition, $attributes = [])
    {
        $query = static::find();
        $query->andWhere($condition);
        $result = $query->one();

        if (!$result) {
            $result = new static([]);
            $result->load($attributes, '');
        }

        return $result;
    }

    public function fields()
    {
        $fields = parent::fields();

        unset($fields['version_start_dt']);
        unset($fields['version_end_dt']);

        return array_merge($fields, []);
    }

    public function behaviors()
    {
        $behaviors = [];
        $behaviors[] = [
            'class' => DatetimeBehavior::className(),
        ];

        if ($this instanceof IModelSoftDelete) {
            $behaviors[] = [
                'class' => SoftDeleteBehavior::className(),
            ];
            $behaviors[] = [
                'class' => \common\behaviors\query\SoftDeleteBehavior::class,
            ];
        }
        if ($this instanceof IHistoryObject) {
            $behaviors[] = [
                'class' => HistoryBehaviors::class,
            ];
        }

        return $behaviors;
    }

    public function beforeSave($insert)
    {
        parent::beforeSave($insert);

        /* @var ActiveRecord|\common\contracts\models\IProtectLoopEntity $current */
        if ($this instanceof IProtectLoopEntity) {
            $attribute = $this::attributeNameParentId();
            $this->checkLoopEntity($this->$attribute, $this->id);
            if ($this->$attribute === $this->id) {
                throw LoopProtectException::makeSelfSignException($attribute);
            }
        }

        return true;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if (!empty($this->_meta)) {
            /** @var MetadataService $service */
            $service = \Yii::$container->get(MetadataService::class);
            foreach ($this->_meta as $metadata) {
                $service->setMetaFromEntity($this, $metadata['key'],
                    $metadata['value']);
            }
        }
    }

    public static function __docAttributeExample()
    {
        $faker = \Faker\Factory::create('ru_RU');

        return [
            'id' => random_int(1, 100),
            [
                '_type' => 'regex',
                'key' => '/(version_start_dt|_dt|_at|_date)$/',
                'value' => function ($key, $matches) {
                    if ($matches[1] === '_date') {
                        return Carbon::now()->format('Y-m-d');
                    }
                    if ($key === 'version_end_dt') {
                        return Carbon::parse('9999-12-31 23:59:59');
                    }

                    return Carbon::now()
                        ->format(Carbon::DEFAULT_TO_STRING_FORMAT);
                },
            ],
            [
                '_type' => 'regex',
                'key' => '/(first|second|third)_name/',
                'value' => function ($key, $matches) use ($faker) {
                    switch ($matches[1]) {
                        case 'first':
                            return $faker->firstName;
                        case 'second':
                            return $faker->lastName;
//                        case "third":
//                            return $faker->middleName;
                    }

                    return null;
                },
            ],
            [
                '_type' => 'regex',
                'key' => '/_fn$/',
                'value' => 'Фамилия Имя Отчество',
            ],
            [
                '_type' => 'regex',
                'key' => '/(_fid|_id|_cid)$/',
                'value' => function ($key, $matches) {
                    return random_int(1, 99);
                },
            ],
            [
                '_type' => 'regex',
                'key' => '/(phone)$/',
                'value' => function ($key, $matches) use ($faker) {
                    return $faker->e164PhoneNumber;
                },
            ],
            [
                '_type' => 'regex',
                'key' => '/(email)$/',
                'value' => function ($key, $matches) use ($faker) {
                    return $faker->email;
                },
            ],
            [
                '_type' => 'regex',
                'key' => '/_ses$/',
                'value' => function ($key, $matches) {
                    return Uuid::uuid();
                },
            ],
        ];
    }

    public static function __docAttributeIgnore()
    {
        return [
            'version_start_dt',
            'version_end_dt',
            'version_actual',
        ];
    }

    public static function __makeDocumentationEntity()
    {
        return DocumentationHelper::ensure(static::class);
    }

    public function __docs(OpenApi $openApi)
    {
        $reflect = new \ReflectionClass($this);
        $schema = new Schema([
            'schema' => $reflect->getShortName(),
            'title' => $reflect->getShortName(),
            'properties' => [],
        ]);

        $docEntity = null;
        if (method_exists($this, '__makeDocumentationEntity')) {
            $docEntity = $this->__makeDocumentationEntity();
        }

        foreach ($docEntity->toArray() as $key => $field) {
            $propertyData = [
                'property' => $key,
                'title' => $docEntity->getAttributeLabel($key),
                'type' => 'string',
                'example' => $field,
            ];

            if ($field === UploadedFile::class) {
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

    public function getRequiredMetaFieldNames()
    {
        return [];
    }

    public function setMeta($attribute, $value)
    {
        /** @var \common\modules\metaInfo\MetadataService $service */
        $service
            = \Yii::$container->get(\common\modules\metaInfo\MetadataService::class);
        $service->setMetaFromEntity($this, $attribute, $value);
    }

    public function getMetaByAttributePattern($pattern = null)
    {
        /** @var \common\modules\metaInfo\MetadataService $service */
        $service
            = \Yii::$container->get(\common\modules\metaInfo\MetadataService::class);

        return $service->getByEntity($this, $pattern);
    }

    public function getMetaFieldValue($field, $default = null)
    {
        /** @var \common\modules\metaInfo\MetadataService $service */
        $service
            = \Yii::$container->get(\common\modules\metaInfo\MetadataService::class);
        $result = $service->getByEntityFieldValue($this, $field);

        return $result ?? $default;
    }

    /**
     * PHP setter magic method.
     * This method is overridden so that AR attributes can be accessed like properties.
     *
     * @param  string  $name  property name
     * @param  mixed  $value  property value
     */
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

    protected function updateInternal($attributes = null)
    {
        /** @var BindingProcedureBehavior $bindingBehavior */
        $bindingBehavior = $this->getBehavior(self::BINDING_UPDATE_INTERNAL);
        if ($bindingBehavior instanceof BindingProcedureBehavior) {
            if (!$this->beforeSave(false)) {
                return false;
            }
            $values = $this->getDirtyAttributes($attributes);
            if (empty($values)) {
                $this->afterSave(false, $values);

                return 0;
            }

            $values = $this->getDirtyAttributes($attributes);
            $result = $bindingBehavior->executeBinding();

            if (!$result) {
                return false;
            }

            $rows = 1;

            $changedAttributes = [];
            foreach ($values as $name => $value) {
                $changedAttributes[$name] = $this->_oldAttributes[$name] ?? null;
                $this->_oldAttributes[$name] = $value;
            }
            $this->afterSave(false, $changedAttributes);

            return $rows;
        }

        return parent::updateInternal($attributes);
    }

    protected function insertInternal($attributes = null)
    {
        /** @var BindingProcedureBehavior $insertBindingBehavior */
        $bindingBehavior = $this->getBehavior(self::BINDING_INSERT_INTERNAL);
        if ($bindingBehavior instanceof BindingProcedureBehavior) {
            if (!$this->beforeSave(true)) {
                return false;
            }
            $values = $this->getDirtyAttributes($attributes);
            $value = $bindingBehavior->executeBinding(true);

            $id = static::getTableSchema()->columns['id']->phpTypecast($value);
            $this->setAttribute('id', $id);
            $values['id'] = $id;

            $changedAttributes = array_fill_keys(array_keys($values), null);
            $this->setOldAttributes($values);
            $this->afterSave(true, $changedAttributes);

            return true;
        }

        return parent::insertInternal($attributes);
    }

    protected function deleteInternal()
    {
        /** @var BindingProcedureBehavior $insertBindingBehavior */
        $bindingBehavior = $this->getBehavior(self::BINDING_DELETE_INTERNAL);
        if ($bindingBehavior instanceof BindingProcedureBehavior) {
            if (!$this->beforeDelete()) {
                return false;
            }
            $value = $bindingBehavior->executeBinding();

            if (!$value) {
                return false;
            }

            $this->setOldAttributes(null);
            $this->afterDelete();

            return 1;
        }

        if ($this instanceof IModelSoftDelete) {
            if (!$this->beforeDelete()) {
                return false;
            }

            $this->afterDelete();

            return 1;
        }

        return parent::deleteInternal();
    }

    protected function resolveFields(array $fields, array $expand)
    {
        $result = parent::resolveFields($fields, $expand);

        $event = new CanReadAttributesEvent([
            'model' => $this,
            'fields' => $result,
        ]);

//        \Yii::$app->trigger(CanReadAttributesEvent::EVENT_CAN_READ_ATTRIBUTE, $event);

        return $event->getOnlyAccessedFields();
    }

    public function validate(
        $attributeNames = null,
        $clearErrors = true,
        $throwException = true
    ) {
        $result = parent::validate($attributeNames, $clearErrors);

        if (!$result && $throwException) {
            throw new ValidationException($this->errors);
        }

        return $result;
    }
}
