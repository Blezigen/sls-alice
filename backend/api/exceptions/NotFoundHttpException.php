<?php

namespace api\exceptions;

use common\contracts\ISwaggerDoc;
use OpenApi\Annotations\OpenApi;
use OpenApi\Annotations\Property;
use OpenApi\Annotations\Schema;

class NotFoundHttpException extends \yii\web\NotFoundHttpException implements ISwaggerDoc
{
    public static function __makeDocumentationEntity()
    {
        return new NotFoundHttpException('Entity not found', 404);
    }

    public static function __docAttributeExample()
    {
        return [];
    }

    public function __docs(OpenApi $openApi)
    {
        $reflect = new \ReflectionClass($this);

        return new Schema([
            'schema' => $reflect->getShortName(),
            'title' => $reflect->getShortName(),
            'description' => "Системная ошибка ({$this->code})",
            'type' => 'object',
            'properties' => [
                new Property([
                    'property' => 'errors',
                    'type' => 'object',
                    'properties' => [
                        new Property([
                            'property' => 'message',
                            'type' => 'object',
                            'description' => 'Сообщение об ошибке',
                            'example' => $this->getMessage(),
                        ]),
                    ],
                ]),
            ],
        ]);
    }

    public static function __docAttributeIgnore()
    {
        return [];
    }
}
