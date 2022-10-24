<?php

namespace common\actions;

use common\modules\filter\FilterQueryParser;
use common\SkipTake;
use yii\data\ActiveDataProvider;
use yii\data\Sort;
use yii\db\ActiveRecord;

class IndexAction extends \yii\rest\IndexAction
{
    /** @var ActiveRecord|mixed */
    public $modelClass;

    public function getQuery()
    {
        return $this->modelClass::find();
    }

    public function checkAccess()
    {
        $user = (string) \Yii::$app->user->id;

        $controller = (string) \Yii::$app->controller->id;
        $module = (string) \Yii::$app->controller->module->id;
        $action = (string) \Yii::$app->controller->action->id;

        $reflect = new \ReflectionClass($this);
        $model = $reflect->getShortName();

        \Yii::debug("access: $user, $module, $controller, $action, $model, *, *");
        if (!\Yii::$app->permission->enforce($user, $module, $controller,
            $action, '*', '*', '*')
        ) {
            throw new \Exception("Нет доступа к модулю `$module` access");
        }
    }

    /**
     * @return \common\ActiveDataProvider
     */
    public function run()
    {
        $controller = \Yii::$app->controller;

        $dataQuery = $this->getQuery();
        if (method_exists($controller, 'getQuery')) {
            $dataQuery = $controller->getQuery();
        }

        $defaultSorts = $this->defaultSort();
        if (method_exists($controller, 'defaultSort')) {
            $defaultSorts = $controller->defaultSort();
        }
        $sorts = $this->sorts();
        if (method_exists($controller, 'sorts')) {
            $sorts = $controller->sorts();
        }

        $sort = new Sort([
            'defaultOrder'    => $defaultSorts,
            'attributes'      => $sorts,
            'enableMultiSort' => true,
        ]);

        $dataQuery->orderBy($sort->orders);

        $service = \Yii::$container->get(FilterQueryParser::class);
        $filters = $service->handle($this->modelClass);

        if ($filters) {
            foreach ($filters as $f) {
                $f->prepare($dataQuery);
            }
        }

        $getRequest = \Yii::$app->request->get();

        $skipTake = null;
        $pagination = false;
        if (array_key_exists("skip" , $getRequest)) {
            $skipTake = new SkipTake([
                "take" => $getRequest['take'] ?? 20,
                "skip" => $getRequest['skip'] ?? 0,
            ]);
        } else {
            $pagination = [
                'pageSizeLimit' => [1, 100],
                'pageSize'      => \Yii::$app->request->get("limit", 20),
            ];
        }

        return new \common\ActiveDataProvider([
            'query'      => $dataQuery,
            'skipTake'   => $skipTake,
            'pagination' => $pagination,
        ]);
    }

    protected function defaultSort()
    {
        return ['id' => SORT_ASC];
    }

    public function sorts()
    {
        return [
            'id',
        ];
    }
}
