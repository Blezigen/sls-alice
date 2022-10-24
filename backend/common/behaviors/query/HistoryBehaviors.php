<?php

namespace common\behaviors\query;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use common\AbstractActiveQuery;
use common\contracts\IHistoryObject;
use yii\base\Behavior;
use yii\base\Event;
use yii\base\ModelEvent;
use yii\console\Application;
use yii\db\ActiveQuery;
use yii\db\ActiveQueryInterface;
use yii\db\ActiveRecord;
use yii\db\QueryBuilder;

/**
 * @method ActiveRecord|ActiveQuery|HistoryBehaviors getTrashed()
 */
class HistoryBehaviors extends Behavior
{
    public function getAccountById()
    {
        if (\Yii::$app instanceof Application) {
            return null;
        }

        return \Yii::$app->user->id;
    }

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_DELETE => 'handlerBeforeDelete',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'handlerBeforeUpdate',
            ActiveRecord::EVENT_BEFORE_INSERT => 'handlerBeforeInsert',
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'handlerBeforeValidate',
        ];
    }

    public function hasMethod($name)
    {
        return method_exists($this, $name);
    }

    public function handlerBeforeDelete(ModelEvent $event)
    {
        /** @var ActiveRecord $sender */
        $sender = $event->sender;
        $sender->updateAttributes([
            'deleted_at' => Carbon::now(),
            'deleted_acc' => $this->getAccountById(),
        ]);

        $event->isValid = true;
    }

    public function handlerBeforeValidate(Event $event)
    {
        /** @var ActiveRecord $sender */
        $sender = $event->sender;
        if ($sender->isNewRecord) {
        }
    }

    public function handlerBeforeUpdate(Event $event)
    {
        /** @var ActiveRecord $sender */
        $sender = $event->sender;
        $sender->load([
            'updated_at' => Carbon::now(),
            'updated_acc' => $this->getAccountById(),
        ], '');
    }

    public function handlerBeforeInsert(Event $event)
    {
        /** @var ActiveRecord $sender */
        $sender = $event->sender;
        if (!\Yii::$app instanceof \yii\console\Application) {
            $sender->load([
                'created_at' => Carbon::now(),
                'created_acc' => $this->getAccountById(),
                'updated_at' => Carbon::now(),
                'updated_acc' => $this->getAccountById(),
            ], '');
        }
    }

    /**
     * Вернуть запись с идентификатором
     *
     * @param $id
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function byId($id)
    {
        /** @var AbstractActiveQuery $entity */
        $entity = $this->owner;

        if (!array_key_exists(ActiveQueryInterface::class,
            class_implements($entity))
        ) {
            throw new \Exception('entity not ActiveQueryInterface');
        }
        $tableName = $entity->modelClass::tableName();
        $entity->andWhere([
            "$tableName.id" => $id,
        ]);

        return $entity;
    }

    public function getHistoryTableName($tableName)
    {
        return preg_replace('/{{%(.*)}}/', '{{%$1_history}}', $tableName);
    }

    /**
     * Вернуть запись версия которой совпадает с датой, или последнюю актуальную версию
     *
     * @param  null  $actualOrDate
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function byVersion($actualOrDate = null)
    {
        /** @var AbstractActiveQuery $entity */
        $entity = $this->owner;

        if (!array_key_exists(ActiveQueryInterface::class,
            class_implements($entity))
        ) {
            throw new \Exception('entity not ActiveQueryInterface');
        }
//        if ($entity instanceof AbstractActiveQuery) {
        $tableName = $entity->getTableName();
        $historyTableName = $this->getHistoryTableName($tableName);

        if (method_exists($entity, 'getHistoryTableName')) {
            $historyTableName = $entity->getHistoryTableName($tableName);
        }
        if (is_null($actualOrDate)) {
            $actualOrDate = Carbon::now();
        }
        $entity->from([$tableName => $historyTableName]);
        try {
            $datetime = Carbon::parse($actualOrDate);
            $uniqueId = uniqid('otd_');
            $entity->andWhere(":$uniqueId between $tableName.version_start_dt and $tableName.version_end_dt");
            $entity->addParams(["$uniqueId" => $datetime]);
        } catch (InvalidFormatException $e) {
            throw $e;
        }
//        }

        return $entity;
    }

    public function prepare(QueryBuilder $builder)
    {
        /** @var ActiveQueryInterface $entity */
        $entity = $this->owner;

        if (!array_key_exists(ActiveQueryInterface::class, class_implements($entity))) {
            throw new \Exception('entity not ActiveQueryInterface');
        }

        $classes = class_implements($entity->modelClass);

        $datetime = null;
        if (\Yii::$app instanceof \yii\web\Application) {
            $datetime = \Yii::$app->request->headers->get('X-Datetime', null);
        }

        if (\Yii::$app instanceof \yii\web\Application) {
            $datetime = \Yii::$app->request->get('x-datetime', $datetime);
        }

        if (array_key_exists(IHistoryObject::class, $classes) && $datetime) {
            $dt = Carbon::parse($datetime);
            $entity->byVersion($dt);
        }
    }
}
