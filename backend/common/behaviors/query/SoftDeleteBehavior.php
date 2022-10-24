<?php

namespace common\behaviors\query;

use common\AbstractActiveQuery;
use common\BaseActiveRecord;
use common\contracts\IHistoryObject;
use common\contracts\IModelSoftDelete;
use yii\base\Behavior;
use yii\base\ModelEvent;
use yii\db\ActiveQueryInterface;
use yii\db\ActiveRecord;

class SoftDeleteBehavior extends Behavior
{
    public function getDeletedAtAttribute($entity)
    {
        $default = 'deleted_at';
        if (property_exists($entity, 'deletedAtAttribute')) {
            return $entity::$deletedAtAttribute ?? $default;
        }

        return $default;
    }

    public function getTrashed()
    {
        /** @var IModelSoftDelete $entity */
        $entity = $this->owner;

        if (!array_key_exists(ActiveQueryInterface::class, class_implements($entity))) {
            throw new \Exception('entity not ActiveQueryInterface');
        }

        return $entity->advanceParams['trashed'];
    }

    public function withTrashed()
    {
        /** @var IModelSoftDelete|ActiveRecord $entity */
        $entity = $this->owner;

        if (!array_key_exists(ActiveQueryInterface::class, class_implements($entity))) {
            throw new \Exception('entity not ActiveQueryInterface');
        }

        $entity->advanceParams['trashed'] = IModelSoftDelete::WITH_TRASHED;

        return $entity;
    }

    public function withoutTrashed()
    {
        /** @var IModelSoftDelete|ActiveRecord $entity */
        $entity = $this->owner;

        if (!array_key_exists(ActiveQueryInterface::class, class_implements($entity))) {
            throw new \Exception('entity not ActiveQueryInterface');
        }

        $entity->advanceParams['trashed'] = IModelSoftDelete::WITHOUT_TRASHED;

        return $entity;
    }

    public function onlyTrashed()
    {
        /** @var ActiveQueryInterface $entity */
        $entity = $this->owner;
        if (array_key_exists(ActiveQueryInterface::class, class_implements($entity))) {
            throw new \Exception('entity not ActiveQueryInterface');
        }

        $entity->advanceParams['trashed'] = IModelSoftDelete::ONLY_TRASHED;

        return $entity;
    }

    public function softDelete()
    {
        /** @var IModelSoftDelete $entity */
        $entity = $this->owner;
        return $entity->delete();
    }

    public function forceDelete()
    {
        /** @var IModelSoftDelete $entity */
        $entity = $this->owner;

        $entity->advanceParams['forceDelete'] = true;
        $result = $entity->delete();
        $entity->advanceParams['forceDelete'] = false;

        return $result;
    }

    public function restore()
    {
        /** @var IModelSoftDelete|BaseActiveRecord $entity */
        $entity = $this->owner;

        if (!$entity->beforeRestore()) {
            return false;
        }

        $attribute = $this->getDeletedAtAttribute($entity);

        if (empty($entity->getOldAttribute($attribute))) {
            $entity->afterRestore();

            return true;
        }

        $this->{$attribute} = null;
        $result = $entity->save();
        $entity->afterRestore();

        return $result;
    }

    public function isTrashed()
    {
        /** @var IModelSoftDelete $entity */
        $entity = $this->owner;
        return !empty($entity->getOldAttribute($this->getDeletedAtAttribute($entity)));
    }

    public function beforeSoftDelete()
    {
        /** @var IModelSoftDelete $entity */
        $entity = $this->owner;

        $event = new ModelEvent();
        $entity->trigger(IModelSoftDelete::EVENT_BEFORE_SOFT_DELETE, $event);

        return $event->isValid;
    }

    public function afterSoftDelete()
    {
        /** @var IModelSoftDelete $entity */
        $entity = $this->owner;

        $event = new ModelEvent();
        $entity->trigger(IModelSoftDelete::EVENT_AFTER_SOFT_DELETE, $event);

        return $event->isValid;
    }

    public function beforeForceDelete()
    {
        /** @var IModelSoftDelete $entity */
        $entity = $this->owner;

        $event = new ModelEvent();
        $entity->trigger(IModelSoftDelete::EVENT_BEFORE_FORCE_DELETE, $event);

        return $event->isValid;
    }

    public function afterForceDelete()
    {
        /** @var IModelSoftDelete $entity */
        $entity = $this->owner;

        $event = new ModelEvent();
        $entity->trigger(IModelSoftDelete::EVENT_AFTER_FORCE_DELETE, $event);

        return $event->isValid;
    }

    public function beforeRestore()
    {
        /** @var IModelSoftDelete $entity */
        $entity = $this->owner;

        $event = new ModelEvent();
        $entity->trigger(IModelSoftDelete::EVENT_BEFORE_RESTORE, $event);

        return $event->isValid;
    }

    public function afterRestore()
    {
        /** @var IModelSoftDelete $entity */
        $entity = $this->owner;

        $event = new ModelEvent();
        $entity->trigger(IModelSoftDelete::EVENT_AFTER_RESTORE, $event);

        return $event->isValid;
    }

    public function prepare($entity)
    {
        /** @var ActiveQueryInterface $entity */
        $entity = $this->owner;

        if (!array_key_exists(ActiveQueryInterface::class, class_implements($entity))) {
            throw new \Exception('entity not ActiveQueryInterface');
        }

        $classes = class_implements($entity->modelClass);
        if (array_key_exists(IHistoryObject::class, $classes) && array_key_exists(IModelSoftDelete::class, $classes)) {
            $tableName = $entity->modelClass::tableName();
            if ($entity instanceof AbstractActiveQuery) {
                $entity->setHistoryQuery();
                $tableName = $entity->getTableName();
            }
            $attribute = $this->getDeletedAtAttribute($entity->modelClass);

            switch ($entity->getTrashed()) {
                case IModelSoftDelete::WITHOUT_TRASHED:
                    $entity->andWhere(['is', "$tableName.$attribute", null]);
                    break;
                case IModelSoftDelete::ONLY_TRASHED:
                    $entity->andWhere(['is not', "$tableName.$attribute", null]);
                    break;
                case IModelSoftDelete::WITH_TRASHED:
                default:
                    break;
            }
        }
    }
}
