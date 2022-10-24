<?php

namespace common\actions;

use common\models\Log;
use common\modules\filter\FilterQueryParser;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;

class LogAction extends \yii\rest\IndexAction
{
    /** @var ActiveRecord|mixed */
    public $modelClass = Log::class;

    public $tablename;

    public function getLogQuery()
    {
        return $this->modelClass::find()->Where(['table_name' => $this->tablename]);
    }

    public function run()
    {
        $controller = \Yii::$app->controller;
        $dataQuery = $this->getLogQuery();
        if (method_exists($controller, 'getLogQuery')) {
            $dataQuery = $controller->getLogQuery();
        }

        $paginator = [
            'limit' => \Yii::$app->params['defaultPageSize'],
        ];

        $paginator = array_merge(
            $paginator,
            \Yii::$app->request->get('paginator', [])
        );

        if (isset($paginator['page'])) {
            \Yii::$app->getRequest()->setQueryParams(
                array_merge(
                    \Yii::$app->request->get(),
                    [
                        'page' => $paginator['page'],
                    ]
                )
            );
        }

        $service = \Yii::$container->get(FilterQueryParser::class);
        $filters = $service->handle($this->modelClass);

        if ($filters) {
            foreach ($filters as $f) {
                $f->prepare($dataQuery);
            }
        }

        return new ActiveDataProvider([
            'query' => $dataQuery,
            'pagination' => [
                'pageSizeLimit' => [1, 100],
                'pageSize' => $paginator['limit'],
                // 'pageSize' => \Yii::$app->params['defaultPageSize'],
            ],
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
