<?php

namespace common\helpers;

use common\contracts\ISwaggerDoc;
use OpenApi\Annotations\JsonContent;
use OpenApi\Annotations\MediaType;
use OpenApi\Annotations\OpenApi;
use OpenApi\Annotations\Schema;
use yii\base\Model;
use yii\web\HttpException;

class SwaggerExceptionResponseBuilder extends Model
{
    public \OpenApi\Annotations\Response $swagger;

    /** @var ISwaggerDoc */
    public $exceptionClass = '';
    public $description = '';
    public $statusCode = 200;
    public $pagination = false;

    public function __construct($exceptionClass, $config = [])
    {
        $this->exceptionClass = $exceptionClass;
        parent::__construct($config);
    }

    public function init()
    {
        parent::init(); 

        /** @var OpenApi $openApi */
        $openApi = \Yii::$app->swagger->getOpenApi();

        $this->swagger = new \OpenApi\Annotations\Response([]);

        /** @var ISwaggerDoc|\Throwable $model */
        $model = $this->exceptionClass::__makeDocumentationEntity();
        $reflect = new \ReflectionClass($this->exceptionClass);

        if (!array_key_exists($reflect->getShortName(), $openApi->components->schemas) && method_exists($model, '__docs')) {
            $docs = $model->__docs($openApi);
            if ($docs instanceof Schema) {
                $openApi->components->schemas[] = $docs;
            }
            if (is_array($docs)) {
                foreach ($docs as $doc) {
                    if ($doc instanceof Schema) {
                        $openApi->components->schemas[] = $doc;
                    }
                }
            }
        }

        $refLink = "#/components/schemas/{$reflect->getShortName()}";

        if ($model instanceof HttpException) {
            $this->swagger = new \OpenApi\Annotations\Response([
                'response' => $model->getCode(),
//                "description" => $model->getDescription(),
                'content' => [
                    'application/json' => new MediaType([
                        'mediaType' => 'application/json',
                        'schema' => new JsonContent([
                            'oneOf' => [
                                new Schema([
                                    'ref' => $refLink,
                                ]),
                            ],
                        ]),
                    ]),
                ],
            ]);
        }
    }

    public function generate()
    {
        return $this->swagger;
    }
}
