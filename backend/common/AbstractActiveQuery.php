<?php

namespace common;

use common\behaviors\query\HistoryBehaviors;
use common\behaviors\query\SoftDeleteBehavior;
use common\contracts\IHistoryObject;
use common\contracts\IModelSoftDelete;
use common\dev\PhpStormHistoryObjectMethods;
use common\dev\PhpStormSoftDeleteMethods;
use yii\db\ActiveQuery;

/**
 * @mixin HistoryBehaviors
 * @mixin SoftDeleteBehavior
 */
abstract class AbstractActiveQuery extends ActiveQuery
{
    protected bool $isHistory = false;

    public array $advanceParams
        = [
            'trashed' => IModelSoftDelete::WITHOUT_TRASHED,
        ];

    public function getTableName()
    {
        return $this->getPrimaryTableName();
    }

    public function setHistoryQuery(): void
    {
        $this->isHistory = true;
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        if (array_key_exists(IHistoryObject::class,
            class_implements($this->modelClass))
        ) {
            $behaviors['history'] = [
                'class' => HistoryBehaviors::class,
            ];
        }
        if (array_key_exists(IModelSoftDelete::class,
            class_implements($this->modelClass))
        ) {
            $behaviors['soft_delete'] = [
                'class' => SoftDeleteBehavior::class,
            ];
        }

        return $behaviors;
    }

    public function __construct($modelClass, $config = [])
    {
        parent::__construct($modelClass, $config);
    }

    public function prepare($builder)
    {
        if (array_key_exists(IModelSoftDelete::class,
            class_implements($this->modelClass))
        ) {
            /** @var SoftDeleteBehavior $behavior */
            $behavior = $this->getBehavior('soft_delete');
            if ($behavior->hasMethod("prepare")) {
                $behavior->prepare($this);
            }
        }

        if (array_key_exists(IHistoryObject::class,
            class_implements($this->modelClass))
        ) {
            /** @var HistoryBehaviors $behavior */
            $behavior = $this->getBehavior('history');
            $behavior->owner = $this;
            if ($behavior->hasMethod("prepare")) {
                $behavior->prepare($builder);
            }
        }

        return parent::prepare($builder);
    }
}
