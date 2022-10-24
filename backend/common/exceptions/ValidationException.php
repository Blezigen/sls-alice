<?php

namespace common\exceptions;

use OpenApi\Annotations\OpenApi;
use OpenApi\Annotations\Property;
use OpenApi\Annotations\Schema;
use Redbox\JsonRpc\Contracts\IRpcException;
use yii\web\HttpException;

class ValidationException extends HttpException implements IRpcException
{
    public $statusCode = 422;
    public array $errors = [];

    public static function __makeDocumentationEntity()
    {
        return new self(['attribute' => ['Validation message ONE', 'Validation message TWO'], 'attribute2' => ['Validation message ONE']]);
    }

    public function __construct(array $errors = [], $prefix = null)
    {
        $this->errors = $errors ?? [];
        $this->code = 422;
        $newErrors = [];
        foreach ($this->errors as $key => $error) {
            $keyPref = $prefix ? "{$prefix}.{$key}" : $key;
            $newErrors[$keyPref] = $error;
        }

        $this->errors = $newErrors;

        parent::__construct($this->statusCode, 'Валидационная ошибка', 0, null);
    }

    public function __docs(OpenApi $openApi)
    {
        $reflect = new \ReflectionClass($this);

        return new Schema([
            'schema' => $reflect->getShortName(),
            'title' => $reflect->getShortName(),
            'description' => "Ошибка валидации ({$this->code})",
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
                        new Property([
                            'property' => 'messages',
                            'type' => 'array',
                            'description' => 'Сообщения об ошибках валидации',
                            'example' => $this->errors,
                        ]),
                    ],
                ]),
            ],
        ]);
    }

    public function fields()
    {
        return [];
    }

    public function extraFields()
    {
        return [];
    }

    public function toArray(
        array $fields = [],
        array $expand = [],
        $recursive = true
    ) {
        return [
            "code" => $this->getCode(),
            "message" => $this->getMessage(),
            "data" => $this->getData(),
        ];
    }

    public function getData()
    {
        return $this->errors;
    }
}
