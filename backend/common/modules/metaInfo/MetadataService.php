<?php

namespace common\modules\metaInfo;

use common\exceptions\ValidationException;
use common\models\Meta;
use common\models\MetaCommonField;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class MetadataService extends Model
{
    public $tables = [];

    public function getValueEntityTable(ActiveRecord $record)
    {
        $tableName = preg_replace("/\{\{\%(.*)\}\}/", '$1',
            $record::tableName());
        $metaValueTableName = "_meta_{$tableName}_field_values";

        if (!in_array($metaValueTableName, $this->tables)) {
            return null;
        }

        return $metaValueTableName;
    }

    public function getHasManyQuery(ActiveRecord $entity)
    {
        $tableName = $this->getValueEntityTable($entity);
        if ($tableName === null) {
            return null;
        }

        return $entity->hasMany(Meta::classname(), ['entity_id' => 'id'])
            ->select([
                '`meta_value`.id as `id`',
                '`meta_field`.attribute as `key`',
                '`meta_field`.title as `title`',
                '`meta_field`.rule as `rule`',
                '`meta_field`.payload as `payload`',
                '`meta_value`.`entity_id` as `entity_id`',
                '`meta_value`.`value` as `value`',
                '`meta_value`.`created_at` as `created_at`',
                '`meta_value`.`updated_at` as `updated_at`',
            ])
            ->from(['meta_value' => $tableName])
            ->innerJoin(['meta_field' => '_meta_common_fields'], 'meta_value.meta_field_id = meta_field.id');
    }

    public function getByEntity(ActiveRecord $record, $pattern = null)
    {
        $metaFieldTableName = '_meta_common_fields';
        $metaValueTableName = $this->getValueEntityTable($record);

        if (!$metaValueTableName) {
            return [];
        }

        $primaryKey = $record->getPrimaryKey();
        $causes = [];

        if ($pattern) {
            $causes[] = [
                'and',
                [
                    'LIKE',
                    "{$metaFieldTableName}.attribute",
                    $pattern,
                ],
                [
                    'is not',
                    "{$metaValueTableName}.value",
                    null,
                ],
            ];
        }

        $query = (new Query())->select([
            "$metaValueTableName.id",
            "$metaFieldTableName.attribute as `key`",
            "$metaFieldTableName.title",
            "$metaFieldTableName.type",
            "$metaFieldTableName.rule",
            "$metaFieldTableName.payload",

            "$metaValueTableName.`value`",
            //            "$metaValueTableName.`created_at`",
            //            "$metaValueTableName.`updated_at`",
        ])
            ->from($metaFieldTableName)
            ->join('left outer join', "$metaValueTableName",
                "`$metaValueTableName`.meta_field_id = `$metaFieldTableName`.id and {$metaValueTableName}.entity_id = :entityId")
            ->where(array_merge([
                'or',
                ['is not', "{$metaValueTableName}.entity_id", null],
                array_merge([
                    'and',
                    [
                        'IN',
                        "{$metaFieldTableName}.attribute",
                        $record->getRequiredMetaFieldNames(),
                    ],
                ], $causes),
            ], []))->addParams(['entityId' => $primaryKey]);

        $result = $query->all();

        foreach ($result as $key => $value) {
            switch ($value['type']) {
                case 'int':
                    $result[$key]['value'] = intval($value['value']);
                    break;
            }
        }

        return $result;
    }

    public function parseIntOrException($attribute, $val)
    {
        if (!is_int($val)) {
            throw new ValidationException([$attribute => ['Value must be an integer.']]);
        }

        return (int) $val;
    }

    public function setMetaFromEntity(ActiveRecord $record, $attribute, $value)
    {
        $metaFieldTableName = '_meta_common_fields';
        $metaValueTableName = $this->getValueEntityTable($record);

        if (!in_array($metaValueTableName, $this->tables)) {
            return false;
        }

        $attributeId = (new Query())->from($metaFieldTableName)
            ->select('id')
            ->andWhere(['attribute' => $attribute])->scalar();

        if (!$attributeId) {
            return false;
        }

        $attributeId = intval($attributeId);

        $query = (new \yii\db\Query())
            ->select([$metaValueTableName . '.id'])
            ->from($metaValueTableName)
            ->where([
                $metaValueTableName . '.meta_field_id' => $attributeId,
                $metaValueTableName . '.entity_id' => $record->getPrimaryKey(),
            ])
            ->limit(1);

        $valueId = $query->scalar();

        $params = [
            'meta_field_id' => $attributeId,
            'entity_id' => $record->getPrimaryKey(),
            'value' => $value,
        ];

        if (!$valueId) {
            $sql = \Yii::$app->db->createCommand()->insert(
                $metaValueTableName,
                $params,
            );
        } else {
            $sql = \Yii::$app->db->createCommand()->update(
                $metaValueTableName,
                $params,
                [
                    'id' => $valueId,
                ],
            );
        }
        $sql->execute();

        return true;
    }

    public function getByEntityFieldValue(
        \common\BaseActiveRecord $record,
        $field
    ) {
        $metaFieldTableName = '_meta_common_fields';
        $metaValueTableName = $this->getValueEntityTable($record);

        if (!in_array($metaValueTableName, $this->tables)) {
            return [];
        }
        $primaryKey = $record->getPrimaryKey();

        $query = (new Query())->select([
            "$metaValueTableName.id",
            "$metaFieldTableName.attribute as `key`",
            "$metaFieldTableName.title",
            "$metaFieldTableName.type",
            "$metaFieldTableName.rule",
            "$metaFieldTableName.payload",

            "$metaValueTableName.`value`",
            //            "$metaValueTableName.`created_at`",
            //            "$metaValueTableName.`updated_at`",
        ])
            ->from($metaFieldTableName)
            ->join('left outer join', "$metaValueTableName",
                "`$metaValueTableName`.meta_field_id = `$metaFieldTableName`.id and {$metaValueTableName}.entity_id = :entityId")
            ->where([
                'and',
                [
                    'or',
                    ['is not', "{$metaValueTableName}.entity_id", null],
                    [
                        'IN',
                        "{$metaFieldTableName}.attribute",
                        $record->getRequiredMetaFieldNames(),
                    ],
                ],
                ["{$metaFieldTableName}.attribute" => $field],
            ])
            ->addParams(['entityId' => $primaryKey]);

        $result = $query->one();

        if (!$result) {
            return null;
        }

        if ($result['value'] === null) {
            return null;
        }

        $return = $result['value'];
        $type = $result['type'] ?? 'n';

        switch ($type) {
            case 'int':
                $return = intval($value ?? null);
                break;
            case 'string':
                $return = "$return";
                break;
            case 'boolean':
                $return = boolval($return);
                break;
        }

        return $return;
    }

    public $requiredFields = [];

    public function getMetadataValue(\common\BaseActiveRecord $param, $metaValues)
    {
        $requiredFields = $param->getRequiredMetaFieldNames();
        $table = $param::tableName();
        $key = $table . '[' . implode(',', $requiredFields) . ']';
        $idx = md5($key);
        if (!array_key_exists($idx, $this->requiredFields)) {
            \Yii::trace("$idx: $key");
            $sqlResults = MetaCommonField::find()->andWhere(['in', 'attribute', $requiredFields])->all();
            $this->requiredFields[$idx] = ArrayHelper::map($sqlResults, 'attribute', function ($model) {
                return $model;
            });
        }

        $data = ArrayHelper::map($metaValues ?? [], 'attribute', function ($model) {
            return $model;
        });

        $result = array_merge($this->requiredFields[$idx], $data);

        return array_values($result);
    }
}
