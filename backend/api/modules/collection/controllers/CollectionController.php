<?php

namespace api\modules\collection\controllers;

use api\exceptions\NotFoundHttpException;
use api\modules\collection\models\read\CollectionRead;
use api\modules\collection\models\write\CollectionWrite;
use common\exceptions\ValidationException;
use common\helpers\SwaggerExceptionResponseBuilder;
use common\helpers\SwaggerHelper;
use common\helpers\SwaggerRequestBuilder;
use common\helpers\SwaggerResponseBuilder;
use doc\ExampleHelper;
use yii\db\ActiveRecord;

class CollectionController extends \api\ApiActiveController
{
    /** @var string|ActiveRecord|CollectionRead */
    public $readModelClass = CollectionRead::class;
    /** @var string|ActiveRecord|CollectionWrite */
    public $writeModelClass = CollectionWrite::class;

    public function __docs($pathUrl, $action)
    {
        switch ($action) {
            // <editor-fold desc="index">
            case 'index':
                return [
                    // 'tags' => ['поиск', 'справочник', 'коллекция'],
                    'summary' => 'Получить абсолютно все справочники.',
                    'description' => 'Для фильтрации справочников воспользуйтесь FQL',
                    'security' => [
                        SwaggerHelper::securityBearerAuth(),
                        SwaggerHelper::securityOAuth(),
                        SwaggerHelper::securityAccessToken(),
                    ],
                    'parameters' => [
                        SwaggerHelper::filterPropertyFromClass('Фильтрация', CollectionWrite::class),
                        SwaggerHelper::headerPropertyByDate(),
                        SwaggerHelper::paginatorProperty(),
SwaggerHelper::sortProperty('Сортировка', $this->sorts()),
                        SwaggerHelper::extraPropertyFromClass('Extend', CollectionWrite::class),
                    ],
                    'responses' => [
                        '200' => (new SwaggerResponseBuilder([
                            'pathUrl' => $pathUrl,
                            'statusCode' => 200,
                            'description' => 'OK',
                            'pagination' => true,
                        ]))->json(CollectionRead::class, false)->generate(),
                        '404' => (new SwaggerExceptionResponseBuilder(NotFoundHttpException::class))->generate(),
                    ],
                ];
            // </editor-fold>
            // <editor-fold desc="update">
            case 'update':
                return [
                    // 'tags' => ['изменение', 'справочник', 'коллекция'],
                    'summary' => 'Изменение элемента справочника.',
                    'description' => 'Позволяет изменять справочник не заполняя все атрибуты.',
                    'security' => [
                        SwaggerHelper::securityBearerAuth(),
                        SwaggerHelper::securityOAuth(),
                        SwaggerHelper::securityAccessToken(),
                    ],
                    'parameters' => [
                        SwaggerHelper::extraPropertyFromClass('Extend', CollectionWrite::class),
                    ],
                    'requestBody' => (new SwaggerRequestBuilder([]))->json(CollectionWrite::class, true)->generate(),
                    'responses' => [
                        '200' => (new SwaggerResponseBuilder([
                            'pathUrl' => $pathUrl,
                            'statusCode' => 200,
                            'description' => 'OK',
                        ]))->json(CollectionRead::class)->generate(),
                        '404' => (new SwaggerExceptionResponseBuilder(NotFoundHttpException::class))->generate(),
                        '422' => (new SwaggerExceptionResponseBuilder(ValidationException::class))->generate(),
                    ],
                ];
            // </editor-fold>
            // <editor-fold desc="delete">
            case 'delete':
                return [
                    // 'tags' => ['удаление', 'справочник', 'коллекция'],
                    'summary' => 'Удаление элемента из справочника.',
                    'security' => [
                        SwaggerHelper::securityBearerAuth(),
                        SwaggerHelper::securityOAuth(),
                        SwaggerHelper::securityAccessToken(),
                    ],
                    'parameters' => [],
                    'responses' => [
                        '204' => (new SwaggerResponseBuilder([
                            'pathUrl' => $pathUrl,
                            'statusCode' => 204,
                            'description' => 'No Content',
                        ]))->generate(),
                        '404' => (new SwaggerExceptionResponseBuilder(NotFoundHttpException::class))->generate(),
                    ],
                ];
            // </editor-fold>
            // <editor-fold desc="view">
            case 'view':
                return [
                    // 'tags' => ['просмотр', 'справочник', 'коллекция'],
                    'summary' => 'Позволяет извлечь информацию по конкретному элементу справочника.',
                    'security' => [
                        SwaggerHelper::securityBearerAuth(),
                        SwaggerHelper::securityOAuth(),
                        SwaggerHelper::securityAccessToken(),
                    ],
                    'parameters' => [
                        SwaggerHelper::headerPropertyByDate(),
                        SwaggerHelper::extraPropertyFromClass('Extend', CollectionWrite::class),
                    ],
                    'responses' => [
                        '200' => (new SwaggerResponseBuilder([
                            'pathUrl' => $pathUrl,
                            'statusCode' => 200,
                            'description' => 'OK',
                        ]))->json(CollectionRead::class)->generate(),
                        '404' => (new SwaggerExceptionResponseBuilder(NotFoundHttpException::class))->generate(),
                    ],
                ];
            // </editor-fold>
            // <editor-fold desc="create">
            case 'create':
                return [
                    // 'tags' => ['создание', 'добавление', 'справочник', 'коллекция'],
                    'summary' => 'Позволяет добавить  элемент в справочник.',
                    'security' => [
                        SwaggerHelper::securityBearerAuth(),
                        SwaggerHelper::securityOAuth(),
                        SwaggerHelper::securityAccessToken(),
                    ],
                    'parameters' => [
                        SwaggerHelper::extraPropertyFromClass('Extend', CollectionWrite::class),
                    ],
                    'requestBody' => (new SwaggerRequestBuilder([
                        'description' => 'OK',
                    ]))->json(CollectionWrite::class, true)->generate(),
                    'responses' => [
                        '200' => (new SwaggerResponseBuilder([
                            'pathUrl' => $pathUrl,
                            'statusCode' => 200,
                            'description' => 'OK',
                        ]))->json(CollectionRead::class)->generate(),
                        '404' => (new SwaggerExceptionResponseBuilder(NotFoundHttpException::class))->generate(),
                        '422' => (new SwaggerExceptionResponseBuilder(ValidationException::class))->generate(),
                    ],
                ];
            // </editor-fold>
        }

        return [];
    }

    public function __examples($action)
    {
        switch ($action) {
            case 'index':
                return ExampleHelper::paginator(CollectionRead::class, random_int(2, 10));
            case 'delete':
                return null;
            case 'update':
            case 'view':
            case 'create':
                return ExampleHelper::data(CollectionRead::class);
        }

        return null;
    }
}
