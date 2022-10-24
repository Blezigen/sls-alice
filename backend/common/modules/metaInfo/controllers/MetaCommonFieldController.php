<?php

namespace common\modules\metaInfo\controllers;

use api\exceptions\NotFoundHttpException;
use common\helpers\SwaggerHelper;
use common\modules\metaInfo\models\read\MetaCommonFieldRead;
use common\modules\metaInfo\models\write\MetaCommonFieldWrite;

class MetaCommonFieldController extends \api\ApiActiveController
{
    public $readModelClass = \common\modules\metaInfo\models\read\MetaCommonFieldRead::class;
    public $writeModelClass = \common\modules\metaInfo\models\write\MetaCommonFieldWrite::class;

    public function __docs($pathUrl, $action)
    {
        switch ($action) {
            // <editor-fold desc="index">
            case 'index':
                return [
                    'summary' => 'Получить все сущности "_meta_common_fields"',
                    'security' => [SwaggerHelper::securityBearerAuth(), SwaggerHelper::securityOAuth(), SwaggerHelper::securityAccessToken()],
                    'parameters' => [
                        SwaggerHelper::filterPropertyFromClass('Фильтрация', MetaCommonFieldWrite::class),
                        SwaggerHelper::sortProperty('Сортировка', $this->sorts()),
                        SwaggerHelper::extraPropertyFromClass('Extend', MetaCommonFieldWrite::class),
                    ],
                    'responses' => [
                        '200' => (new \common\helpers\SwaggerResponseBuilder([
                            'pathUrl' => $pathUrl,
                            'statusCode' => 200,
                            'description' => 'OK',
                            'pagination' => true,
                        ]))->json(MetaCommonFieldRead::class, false)->generate(),
                        '404' => (new \common\helpers\SwaggerExceptionResponseBuilder(NotFoundHttpException::class))->generate(),
                    ],
                ];
            // </editor-fold>
            // <editor-fold desc="update">
            case 'update':
                return [
                    'summary' => 'Частичное обновление сущности "_meta_common_fields"',
                    'security' => [SwaggerHelper::securityBearerAuth(), SwaggerHelper::securityOAuth(), SwaggerHelper::securityAccessToken()],
                    'parameters' => [
                        SwaggerHelper::extraPropertyFromClass('Extend', MetaCommonFieldWrite::class),
                    ],
                    'requestBody' => (new \common\helpers\SwaggerRequestBuilder([]))->json(MetaCommonFieldWrite::class, true)->generate(),
                    'responses' => [
                        '200' => (new \common\helpers\SwaggerResponseBuilder([
                            'pathUrl' => $pathUrl,
                            'statusCode' => 200,
                            'description' => 'OK',
                        ]))->json(MetaCommonFieldRead::class)->generate(),
                        '404' => (new \common\helpers\SwaggerExceptionResponseBuilder(NotFoundHttpException::class))->generate(),
                    ],
                ];
            // </editor-fold>
            // <editor-fold desc="delete">
            case 'delete':
                return [
                    'summary' => 'Удаление сущности "_meta_common_fields"',
                    'security' => [SwaggerHelper::securityBearerAuth(), SwaggerHelper::securityOAuth(), SwaggerHelper::securityAccessToken()],
                    'parameters' => [],
                    'responses' => [
                        '204' => (new \common\helpers\SwaggerResponseBuilder([
                            'pathUrl' => $pathUrl,
                            'statusCode' => 204,
                            'description' => 'No Content',
                        ]))->generate(),
                        '404' => (new \common\helpers\SwaggerExceptionResponseBuilder(NotFoundHttpException::class))->generate(),
                    ],
                ];
            // </editor-fold>
            // <editor-fold desc="view">
            case 'view':
                return [
                    'summary' => 'Получение конкретной сущности "_meta_common_fields"',
                    'security' => [SwaggerHelper::securityBearerAuth(), SwaggerHelper::securityOAuth(), SwaggerHelper::securityAccessToken()],
                    'parameters' => [
                        SwaggerHelper::headerPropertyByDate(),
                        SwaggerHelper::extraPropertyFromClass('Extend', MetaCommonFieldWrite::class),
                    ],
                    'responses' => [
                        '200' => (new \common\helpers\SwaggerResponseBuilder([
                            'pathUrl' => $pathUrl,
                            'statusCode' => 200,
                            'description' => 'OK',
                        ]))->json(MetaCommonFieldRead::class)->generate(),
                        '404' => (new \common\helpers\SwaggerExceptionResponseBuilder(NotFoundHttpException::class))->generate(),
                    ],
                ];
            // </editor-fold>
            // <editor-fold desc="create">
            case 'create':
                return [
                    'summary' => 'Создание сущности "_meta_common_fields"',
                    'security' => [SwaggerHelper::securityBearerAuth(), SwaggerHelper::securityOAuth(), SwaggerHelper::securityAccessToken()],
                    'parameters' => [
                        SwaggerHelper::extraPropertyFromClass('Extend', MetaCommonFieldWrite::class),
                    ],
                    'requestBody' => (new \common\helpers\SwaggerRequestBuilder([
                        'description' => 'OK',
                    ]))->json(MetaCommonFieldWrite::class, true)->generate(),
                    'responses' => [
                        '200' => (new \common\helpers\SwaggerResponseBuilder([
                            'pathUrl' => $pathUrl,
                            'statusCode' => 200,
                            'description' => 'OK',
                        ]))->json(MetaCommonFieldRead::class)->generate(),
                        '404' => (new \common\helpers\SwaggerExceptionResponseBuilder(NotFoundHttpException::class))->generate(),
                    ],
                ];
            // </editor-fold>
        }

        return [];
    }
}
