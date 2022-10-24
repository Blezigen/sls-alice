<?php

namespace doc\modules\swagger;

use yii\console\Application;

class Module extends \common\AbstractModule
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'doc\modules\swagger\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        if (\Yii::$app instanceof Application) {
            $this->controllerNamespace = "doc\modules\swagger\commands";
        }

        \Yii::$app->setComponents([
            'swagger' => [
                'class' => SwaggerManager::class,
                'servers' => [
                    [
                        'url' => env('API_URL'),
                        'description' => 'Endpoint рабочего API',
                    ],
                    [
                        'url' => env('DOC_API_URL'),
                        'description' => 'Endpoint с заглушками',
                    ],
                ],
                'verbs' => [
                ],
            ],
        ]);
    }

    public function routes($moduleID)
    {
        $moduleID = $this->id;

        return [
            [
                'class' => \yii\web\UrlRule::class,
                'pattern' => '/',
                'route' => "{$moduleID}/default/index",
            ],
            [
                'class' => \yii\web\UrlRule::class,
                'pattern' => '/oauth',
                'route' => "{$moduleID}/default/oauth2-redirect",
            ],
            [
                'class' => \yii\web\UrlRule::class,
                'pattern' => '/swagger<type>',
                'route' => "{$moduleID}/file/version",
            ],
        ];
    }
}
