<?php

namespace common\helpers;

use common\contracts\ISwaggerDoc;
use OpenApi\Annotations\JsonContent;
use OpenApi\Annotations\MediaType;
use OpenApi\Annotations\OpenApi;
use OpenApi\Annotations\Property;
use OpenApi\Annotations\Schema;
use yii\base\Model;

class SwaggerResponseBuilder extends Model
{
    public \OpenApi\Annotations\Response $swagger;

    public $pathUrl = '';
    public $description = '';
    public $statusCode = 200;
    public $pagination = false;

    public function init()
    {
        parent::init(); 

        $this->swagger = new \OpenApi\Annotations\Response([
            'response' => $this->statusCode,
            'description' => $this->description,
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
    public function json($ref = null, $single = false, $randomElementCount = 10)
    {
        $items = [];

        if (!$ref) {
            throw new \Exception('ref is null!');
        }

        /** @var OpenApi $openApi */
        $openApi = \Yii::$app->swagger->getOpenApi();

        if (!method_exists($ref, '__makeDocumentationEntity')) {
            throw new \Exception("$ref method __makeDocumentationEntity not exist!");
        }

        if ($single) {
            /** @var ISwaggerDoc $model */
            $model = $ref::__makeDocumentationEntity();
            $items = $model->toArray();
        } else {
            for ($i = 0; $i < $randomElementCount; ++$i) {
                /** @var ISwaggerDoc $model */
                $model = $ref::__makeDocumentationEntity();
                $items[] = $model->toArray();
            }
        }

        $properties = [
            new Property([
                'property' => 'data',
                'type' => $single ? 'object' : 'array',
                'example' => $items,
            ]),
        ];

        if ($this->pagination) {
            $properties['_links'] = new Property([
                'property' => '_links',
                'type' => 'object',
                'example' => [
                    'self' => [
                        'href' => "https://{server}/{$this->pathUrl}?paginator[page]=1&paginator[limit]=$randomElementCount",
                    ],
                    'first' => [
                        'href' => "https://{domain}/{$this->pathUrl}?paginator[page]=1&paginator[limit]=$randomElementCount",
                    ],
                    'last' => [
                        'href' => "https://{domain}/{$this->pathUrl}?paginator[page]=1&paginator[limit]=$randomElementCount",
                    ],
                    'next' => [
                        'href' => "https://{domain}/{$this->pathUrl}?paginator[page]=1&paginator[limit]=$randomElementCount",
                    ],
                ],
            ]);
            $properties['_meta'] = new Property([
                'property' => '_meta',
                'type' => 'object',
                'example' => [
                    'totalCount' => $randomElementCount,
                    'pageCount' => 1,
                    'currentPage' => 1,
                    'perPage' => $randomElementCount,
                ],
            ]);
        }

        $this->swagger->content['application/json'] = new MediaType([
            'mediaType' => 'application/json',
            'schema' => new JsonContent([
                'oneOf' => [
                    new Schema([
                        'type' => 'object',
                        'properties' => $properties,
                    ]),
                ],
            ]),
        ]);

        return $this;
    }

    public function generate()
    {
        return $this->swagger;
    }
}
