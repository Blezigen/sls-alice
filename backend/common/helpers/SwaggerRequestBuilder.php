<?php

namespace common\helpers;

use api\modules\order_module\exec_actions\CreateFromTempOrderExecAction;
use common\contracts\ISwaggerDoc;
use doc\DocumentationHelper;
use Jasny\PhpdocParser\PhpdocParser;
use Jasny\PhpdocParser\Set\PhpDocumentor;
use Jasny\PhpdocParser\Tag\DescriptionTag;
use Jasny\PhpdocParser\Tag\FlagTag;
use Jasny\PhpdocParser\Tag\PhpDocumentor\TypeTag;
use Jasny\PhpdocParser\Tag\Summery;
use OpenApi\Annotations\Items;
use OpenApi\Annotations\JsonContent;
use OpenApi\Annotations\MediaType;
use OpenApi\Annotations\OpenApi;
use OpenApi\Annotations\Property;
use OpenApi\Annotations\Schema;
use phpDocumentor\Reflection\FqsenResolver;
use phpDocumentor\Reflection\Types\ContextFactory;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class SwaggerRequestBuilder extends Model
{
    public \OpenApi\Annotations\RequestBody $swagger;

    public $pathUrl = '';
    public $description = '';

    public function init()
    {
        parent::init(); 

        $this->swagger = new \OpenApi\Annotations\RequestBody([
            'content' => [],
        ]);
    }

    /**
     * @param  ISwaggerDoc|string|null  $ref
     *
     * @return $this
     *
     * @throws \ReflectionException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function json($ref = null, $single = true)
    {
        $items = null;

        $refLink = null;
        if ($ref) {
            /** @var OpenApi $openApi */
            $openApi = \Yii::$app->swagger->getOpenApi();

            if (in_array(ISwaggerDoc::class, class_implements($ref))) {
                $reflect = new \ReflectionClass($ref);
                if (!array_key_exists($reflect->getShortName(),
                    $openApi->components->schemas)
                ) {
                    if (in_array(ISwaggerDoc::class, class_implements($ref))) {
                        /** @var ISwaggerDoc $model */
                        $model = $ref::__makeDocumentationEntity();
                        $docs = $model->__docs($openApi);

                        if ($docs instanceof Schema) {
                            $openApi->components->schemas[$reflect->getShortName()]
                                = $docs;
                        }
                    }
                }
                $refLink = "#/components/schemas/{$reflect->getShortName()}";
            }
        }

        $schemas = [
            new Schema([
                'type' => 'object',
                'ref' => $refLink,
            ]),
        ];

        if (!$single) {
            $items = new Items([
                'ref' => $refLink,
            ]);

            $properties = [
                new Property([
                    'property' => 'data',
                    'type' => 'array',
                    'items' => $items,
                ]),
            ];

            $schemas = [
                new Schema([
                    'type' => 'object',
                    'properties' => $properties,
                ]),
            ];
        }

        $this->swagger->content['application/json'] = new MediaType([
            'mediaType' => 'application/json',
            'schema' => new JsonContent([
                'oneOf' => $schemas,
            ]),
        ]);

        return $this;
    }

    /**
     * @param  ISwaggerDoc|string|null  $ref
     *
     * @return $this
     *
     * @throws \ReflectionException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function formData($ref)
    {
        /** @var OpenApi $openApi */
        $openApi = \Yii::$app->swagger->getOpenApi();

        $reflect = new \ReflectionClass($ref);
        if (!array_key_exists($reflect->getShortName(),
            $openApi->components->schemas)
        ) {
            if (in_array(ISwaggerDoc::class, class_implements($ref))) {
                /** @var ISwaggerDoc $model */
                $model = $ref::__makeDocumentationEntity();
                $docs = $model->__docs($openApi);

                if ($docs instanceof Schema) {
                    $openApi->components->schemas[$reflect->getShortName()] = $docs;
                }
            }
        }
        $schema = $openApi->components->schemas[$reflect->getShortName()];
        $this->swagger->content['multipart/form-data'] = new MediaType([
            'mediaType' => 'multipart/form-data',
            'schema' => new Schema([
                'type' => 'object',
                'properties' => $schema->properties,
            ]),
        ]);

        return $this;
    }

    /**
     * @param  ISwaggerDoc|string|null  $ref
     *
     * @return $this
     *
     * @throws \ReflectionException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function formDataField($description, $property, $type)
    {
        if (!isset($this->swagger->content['multipart/form-data'])) {
            $this->swagger->required = true;
            $this->swagger->content['multipart/form-data'] = new MediaType([
                'mediaType' => 'multipart/form-data',
                'schema' => new Schema([
                    'type' => 'object',
                    'properties' => [],
                ]),
            ]);
        }

        $this->swagger->content['multipart/form-data']->schema->properties[] = new Property([
            'property' => $property,
            'description' => $description,
            'type' => $type,
        ]);

        return $this;
    }

    public function exception($ref)
    {
        $reflect = new \ReflectionClass($ref);
        $refLink = "#/components/schemas/{$reflect->getShortName()}";
        $this->swagger->content['application/json'] = new MediaType([
            'mediaType' => 'application/json',
            'schema' => new Schema([
                'ref' => $refLink,
            ]),
        ]);

        return $this;
    }

    public function generate()
    {
        return $this->swagger;
    }

    public function getValue($value)
    {
        if (is_array($value)) {
            $result = [];
            foreach ($value as $item) {
                $result[] = $this->getValue($item);
            }

            return $result;
        } elseif (is_bool($value)) {
            $value = $value ? "true" : "false";
            return "<span class='hljs-keyword'>$value</span>";
        } elseif (is_string($value)) {
            return "<span class='hljs-string'>\"$value\"</span>";
        } elseif (is_numeric($value)) {
            return "<span class='hljs-number'>$value</span>";
        } elseif (is_null($value)) {
            return "<span class='hljs-literal'>null</span>";
        }
    }

    public function getRpcFunctions($actions)
    {
        $result = [];
        foreach ($actions as $actionKey => $action) {
            $entity = $action['class'];

            /** @var ISwaggerDoc $obj */
            $actionObject = new $entity(
                $actionKey,
                \Yii::$app->controller
            );

            $class = new \ReflectionClass($entity);
            $method = new \ReflectionMethod($actionObject, 'run');

            if (method_exists($actionObject, '__params')) {
                $paramDescription = $actionObject->__params();
            }

            if (method_exists($actionObject, '__example')) {
                $examples = $actionObject->__example();
            }

            $meta = (new PHPDocParser(PhpDocumentor::tags()->with([
                new Summery()
            ])))->parse($class->getDocComment());
            $desc = $meta["summery"] ?? "Описание";

            $methodMeta = (new PHPDocParser(PhpDocumentor::tags()->with([
                new Summery()
            ])))->parse($method->getDocComment());

            $params = $methodMeta["params"] ?? [];
            $signatureParams = [];
            foreach ($method->getParameters() as $key => $param) {
                $name = $param->getName();
                $paramType = $param->getType();
                $params[$name]['name'] = $name;

                $types = "mixed";
                if ($paramType instanceof \ReflectionNamedType) {
                    $types = implode('|',[$paramType->getName()]);
                }
                if ($paramType instanceof \ReflectionUnionType) {
                    $types = ArrayHelper::getColumn($paramType->getTypes(), function ($data) {
                        return $data->getName();
                    });
                    $types = implode('|',$types);
                }

                $type = $params[$name]["type"]??null;
                $description = $params[$name]["description"]??null;
                if (!$type) {
                    $params[$name]['type'] = $types;
                }
                if (!$description) {
                    $params[$name]['description'] = $paramDescription[$name] ?? "$paramType";
                }
//                if (array_key_exists("type", $methodMeta)){
//                    $params[$name]['type'] = $methodMeta["type"];
//                }

                $params[$name]['example'] = DocumentationHelper::getExampleValue($name, $examples ?? []);
                $sign = "<span class='hljs-keyword'>{$params[$name]['type']}</span> <span class='hljs-variable'>\${$name}</span>";
                if ($param->isDefaultValueAvailable()) {
                    $params[$name]['default'] = $param->getDefaultValue();

                    if (is_array($param->getDefaultValue())) {
                        $sign .= ' = ' . '[' . implode(',', $this->getValue($param->getDefaultValue())) . ']';
                    }
                    else {
                        $sign .= ' = ' . $this->getValue($param->getDefaultValue() ?? null);
                    }
                }
                $signatureParams[] = $sign;
            }
            $result[] = [
                'method' => $actionKey,
                'params' => $params,
                'description' => $desc ?? null,
                'signature' => "<span class='hljs-function'><span class='hljs-keyword'>function</span> <span class='hljs-title'>$actionKey</span>(" . implode(', ', $signatureParams) . ')</span>',
            ];
        }

        return $result;
    }

    public function rpc($controller, array $actions)
    {
        /** @var OpenApi $openApi */
        $openApi = \Yii::$app->swagger->getOpenApi();
        $schema = new Schema([
            'schema' => md5(get_class($controller)) . 'execs',
            'type' => 'array',
            'items' => new Schema([
                    'type' => 'object',
                    'properties' => [
                        'jsonrpc' => [
                            'property' => 'jsonrpc',
                            'type' => 'string',
                        ],
                        'id' => [
                            'property' => 'id',
                            'type' => 'string',
                        ],
                        'method' => [
                            'property' => 'method',
                            'type' => 'string',
                        ],
                        'params' => [
                            'property' => 'params',
                            'type' => 'string',
                        ],
                    ],
                ]),
            'example' => [
            ],
        ]);

        $openApi->components->schemas[md5(get_class($controller)) . 'execs'] = $schema;
        $refLink = '#/components/schemas/' . (md5(get_class($controller)) . 'execs');

        $actions = $this->getRpcFunctions($actions);

        foreach ($actions as $actionKey => $action) {
            $example = ArrayHelper::getColumn($action['params'], 'example');
            $schema->example[] = [
                'jsonrpc' => '2.0',
                'id' => uniqid('id_'),
                'method' => $action['method'],
                'params' => $example,
            ];
        }

        $this->swagger->content['application/json+rpc-2.0'] = new MediaType([
            'mediaType' => 'application/json+rpc-2.0',
            'schema' => new JsonContent([
                'ref' => $refLink,
            ]),
        ]);

        return $this;
    }
}
