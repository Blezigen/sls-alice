<?php

namespace common;

use api\Request;
use common\actions\CreateAction;
use common\actions\DeleteAction;
use common\actions\IndexAction;
use common\actions\MetaActions;
use common\actions\OptionsAction;
use common\actions\UpdateAction;
use common\actions\ViewAction;
use common\contracts\IHistoryObject;
use common\contracts\IModelSoftDelete;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

/**
 * @property mixed $query
 * @property Request $request
 *
 * @package common
 */
abstract class AbstractActiveController extends AbstractController
{
    /**
     * @var string the scenario used for updating a model
     *
     * @see \yii\base\Model::scenarios()
     */
    public string $updateScenario = Model::SCENARIO_DEFAULT;
    /**
     * @var string the scenario used for creating a model
     *
     * @see \yii\base\Model::scenarios()
     */
    public string $createScenario = Model::SCENARIO_DEFAULT;

    /** @var ActiveRecord|string|mixed */
    public $readModelClass = null;

    /** @var ActiveRecord|string|mixed */
    public $writeModelClass = null;

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'index' => [
                'class' => IndexAction::class,
                'modelClass' => $this->readModelClass,
            ],
            'meta' => [
                'class' => MetaActions::class,
                'modelClass' => $this->readModelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'findModel' => [$this, 'findModel'],
            ],
            'view' => [
                'class' => ViewAction::class,
                'modelClass' => $this->readModelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'findModel' => [$this, 'findModel'],
            ],
            'create' => [
                'class' => CreateAction::class,
                'modelClass' => $this->writeModelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'transformDataProvider' => [$this, 'transformDataProvider'],
                'scenario' => $this->createScenario,
            ],
            'update' => [
                'class' => UpdateAction::class,
                'modelClass' => $this->writeModelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'findModel' => [$this, 'findModel'],
                'scenario' => $this->updateScenario,
            ],
            'delete' => [
                'class' => DeleteAction::class,
                'modelClass' => $this->writeModelClass,
                'findModel' => [$this, 'findModel'],
                'checkAccess' => [$this, 'checkAccess'],
            ],
            'options' => [
                'class' => OptionsAction::class,
            ],
        ];
    }

    public function getQuery()
    {
        return $this->readModelClass::find();
    }

    public function findModel($id)
    {
        $query = $this->getQuery();

        if (array_key_exists(IHistoryObject::class, class_implements($this->readModelClass))) {
            /* @var AbstractActiveQuery $query */
            $query->byId($id);
        } else {
            /** @var ActiveQuery $query */
            $tableName = $this->readModelClass::tableName();
            $query->andWhere(["$tableName.id" => $id]);
        }

        if (array_key_exists(IModelSoftDelete::class, class_implements($this->readModelClass))) {
            /* @var AbstractActiveQuery $query */
            $query->withoutTrashed();
        }

        return $query->one();
    }

    public function transformDataProvider($data)
    {
        return $data;
    }

    public function sorts()
    {
        $tableName = $this->readModelClass::tableName();

        return [
            'id' => [
                'asc' => ["$tableName.id" => SORT_ASC],
                'desc' => ["$tableName.id" => SORT_DESC],
                'default' => SORT_DESC,
                'label' => 'ID',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if ($this->readModelClass === null) {
            throw new InvalidConfigException('The "readModelClass" property must be set.');
        }
        if ($this->writeModelClass === null) {
            throw new InvalidConfigException('The "writeModelClass" property must be set.');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD'],
            'view' => ['GET', 'HEAD'],
            'create' => ['POST'],
            'update' => ['PUT', 'PATCH'],
            'delete' => ['DELETE'],
        ];
    }

    /**
     * Checks the privilege of the current user.
     *
     * This method should be overridden to check whether the current user has the privilege
     * to run the specified action against the specified data model.
     * If the user does not have access, a [[ForbiddenHttpException]] should be thrown.
     *
     * @param  string  $action  the ID of the action to be executed
     * @param  object  $model  the model to be accessed. If null, it means no specific model is being accessed.
     * @param  array  $params  additional parameters
     *
     * @throws ForbiddenHttpException if the user does not have access
     */
    public function checkAccess($action, $model = null, $params = [])
    {
    }

    public function afterAction($action, $result)
    {
        $result = parent::afterAction($action, $result);
        switch ($action->id) {
            case 'view':
            case 'update':
                return [
                    'data' => $result,
                ];
        }

        return $result;
    }

    public function runAction($id, $params = [])
    {
        $response = \Yii::$app->getResponse();
        $response->format = Response::FORMAT_JSON;
        try {
            Timings::check('action');

            $result = parent::runAction($id, $params);

            Timings::check('action');
            \Yii::$app->response->headers->add('Server-Timing',
                Timings::getData());

            return $result;
        } catch (\Throwable $ex) {
            throw $ex;
        }
    }

    public function defaultSort()
    {
        return ['id' => SORT_ASC];
    }
}
