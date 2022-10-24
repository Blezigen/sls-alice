<?php

namespace doc\modules\swagger;

use api\helpers\UrlRule;
use common\helpers\SwaggerHelper;
use OpenApi\Annotations\Components;
use OpenApi\Annotations\Delete;
use OpenApi\Annotations\Flow;
use OpenApi\Annotations\Get;
use OpenApi\Annotations\Head;
use OpenApi\Annotations\Info;
use OpenApi\Annotations\OpenApi;
use OpenApi\Annotations\Options;
use OpenApi\Annotations\Patch;
use OpenApi\Annotations\PathItem;
use OpenApi\Annotations\Post;
use OpenApi\Annotations\Put;
use OpenApi\Annotations\SecurityScheme;
use OpenApi\Annotations\Server;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\web\Controller;

class SwaggerManager extends Model
{
    /** @var OpenApi|null */
    protected $openApiClient = null;
    protected $_servers = [];
    protected $_changes = null;
    public $verbs = null;
    /**
     * @var mixed
     */
    protected $_composer = null;
    /**
     * @var mixed
     */
    public $enableCache = false;
    public $includePut = false;
    public $includeOptions = false;
    public $includeHead = false;

    public function setServers(array $servers)
    {
        $openApi = $this->getOpenApi();
        foreach ($servers as $server) {
            if (is_array($server)) {
                $this->_servers[] = new Server([
                    'url' => $server['url'] ?? '',
                    'description' => $server['description'] ??
                        "Сервер {$server['url']}",
                    'variables' => $server['params'] ?? [],
                ]);
                continue;
            }
            if (is_string($server)) {
                $this->_servers[] = new Server([
                    'url' => $server,
                    'variables' => [],
                ]);
            }
        }
        $openApi->servers = $this->_servers;
    }

    public function getOpenApi(): ?OpenApi
    {
        if (!$this->openApiClient) {
            $this->openApiClient = new OpenApi([
                'info' => new Info([]),
                'components' => new Components([
                    'schemas' => [],
                    'securitySchemes' => [
                        new SecurityScheme([
                            'securityScheme' => 'BearerAuth',
                            'type' => 'http',
                            'scheme' => 'bearer',
                        ]),
                        new SecurityScheme([
                            'securityScheme' => 'ApiKey',
                            'type' => 'apiKey',
                            'name' => 'access-token',
                            'in' => 'query',
                        ]),
                        new SecurityScheme([
                            'securityScheme' => 'OAuth2',
                            'type' => 'oauth2',
                            'flows' => [
                                new Flow([
                                    'authorizationUrl' => 'api/oauth/authorize',
                                    'flow' => 'implicit',
                                    'scopes' => [],
                                ]),
                            ],
                        ]),
                    ],
                ]),
            ]);
        }

        return $this->openApiClient;
    }

    public function loadComposer()
    {
        if (!$this->_composer) {
            $path = \Yii::getAlias('@root/composer.json');
            $content = file_get_contents(realpath($path));
            $this->_composer = json_decode($content);
        }
    }

    public function getAppName()
    {
        $this->loadComposer();

        return $this->_composer->description ??
            "composer.json 'description' not found";
    }

    private function saveComposer()
    {
        $path = \Yii::getAlias('@root/composer.json');
        file_put_contents(realpath($path), json_encode($this->_composer,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            | JSON_PRETTY_PRINT));
    }

    public function getServers()
    {
        return $this->_servers;
    }

    public function getCurrentVersion()
    {
        $changes = $this->getChanges();
        ArrayHelper::multisort($changes, ['version', 'date'], SORT_ASC);

        $version = $changes[array_key_last($changes)]['version'] ?? '0';

        return $version;
    }

    public function getNextVersion()
    {
        $version = $this->getCurrentVersion();
        $version = str_replace('.', '', $version) + 1;
        $version = implode('.',
            str_split(str_pad($version, 3, '0', STR_PAD_LEFT)));

        return $version;
    }

    public function saveCanges()
    {
        $version = '';
        ArrayHelper::multisort($this->_changes, ['version', 'date'], SORT_ASC);
        $path = \Yii::getAlias('@api/changes.json');
        file_put_contents($path, json_encode($this->_changes,
            JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        \Yii::$app->swagger->loadComposer();
        $this->_composer->version = $this->getCurrentVersion();
        $this->saveComposer();

        return true;
    }

    public function addChange(
        $changeDescription,
        $version = 'auto',
        $date = null
    ) {
        $this->getChanges();
        if ($version == 'auto') {
            $version = $this->getNextVersion();
        }

        $this->_changes[] = [
            'date' => $date ?? date('d.m.Y'),
            'version' => $version,
            'description' => $changeDescription,
        ];
    }

    public function getChanges()
    {
        if (!$this->_changes) {
            $this->loadChanges();
        }

        return $this->_changes;
    }

    public function getDescriptionChanges()
    {
        $changes = $this->getChanges();
        $desc = '# История изменений' . PHP_EOL;
        $desc .= "<table class='table table-striped'>" . PHP_EOL;
        $desc .= ' <thead><tr><td><b>Дата</b></td><td><b>Версия</b></td><td><b>Изменения</b></td></tr></thead>'
            . PHP_EOL;
        $desc .= '<tbody>' . PHP_EOL;
        foreach ($changes as $change) {
            $changeText = '';
            if (is_array($change['description'])) {
                foreach ($change['description'] as $key => $value) {
                    if (!is_array($value)) {
                        $changeText .= $value . '<br>';
                        continue;
                    }
                    $type = $value['type'] ?? 'default';
                    switch ($type) {
                        case 'exec':
                            $changeText .= "[$type] " . ($value['description'] ?? '') . '<br>';
                            break;
                        case 'default':
                            $changeText .= ($value['description'] ?? '') . '<br>';
                            break;
                    }
                }
            } else {
                $changeText = $change['description'];
            }

            $desc .= "<tr data-version='{$change['version']}'><td>{$change['date']}</td><td><a href='?version=0.0.1'>{$change['version']}</a></td><td>{$changeText}</td></tr>";
        }
        $desc .= "<tr><td colspan='3'><a href='?version=latest'><button class='btn btn-default'>Вернуться на последнюю версию</button></a></td></tr>";
        $desc .= '</tbody>' . PHP_EOL;
        $desc .= '</table>' . PHP_EOL;

        return $desc;
    }

    public function process($version = 'latest')
    {
        $openApi = $this->getOpenApi();

        \Yii::$app->swagger->loadComposer();
        $openApi->info->title = $this->getAppName();

        $staticDoc = $this->getDescriptionChanges() . PHP_EOL . PHP_EOL . PHP_EOL
            . file_get_contents(\Yii::getAlias('@api/readme.md'));
        $openApi->info->description = $staticDoc;
        $openApi->info->version = $this->_composer->version;

        $paths = [];

        /** @var UrlRule[] $rules */
        $rules = \Yii::$app->urlManager->rules;
        foreach ($rules as $rule) {
            /** @var Controller $controller */
            list($controller, $action)
                = \Yii::$app->createController($rule->route, []);

            if (!$controller) {
                continue;
            }

            $module = $controller->module;

            if (!method_exists($controller, '__docs')) {
                continue;
            }

            $pathUrl = $rule->name;

            preg_match_all("/(?<full>\<(?<name>[^\>^:]+)(?:\:(?<regex>[^\>]+))?\>)/",
                $pathUrl, $matches, PREG_SET_ORDER);

            if (!empty($matches)) {
                foreach ($matches as $match) {
                    $name = $match['name'] ?? '';
                    $pathUrl = str_replace($match['full'], '{' . $name . '}',
                        $pathUrl);
                }
            }

            $tags = [$module->id];

            $path = new PathItem([
                'path' => "/$pathUrl",
            ]);

            if (array_key_exists("$pathUrl", $paths)) {
                $path = $paths["$pathUrl"];
            }
            $documentation = [];
            if (is_callable([$controller, '__docs'])) {
                $documentation = $controller->__docs($pathUrl, $action);
            }

            $routeParameters = [];
            if ($rule instanceof UrlRule && !empty($rule->getParams())) {
                foreach ($rule->getParams() as $param => $regex) {
                    $routeParameters[] = SwaggerHelper::pathProperty($param,
                        'regex:<code>' . str_replace('#', '/', $regex) . '</code>');
                }
            }

            if (\Yii::$app->request->get('debug') === 'true') {
                $routeParameters[] = SwaggerHelper::headerProperty('x-debug',
                    'true', ['example' => 'true']);
            }

            if (in_array('GET', $rule->verb)) {
                $path->get = new Get(ArrayHelper::merge($documentation, [
                    'tags' => $tags,
                    'parameters' => $routeParameters,
                ]));
            }
            if (in_array('POST', $rule->verb)) {
                $path->post = new Post(ArrayHelper::merge([
                    'tags' => $tags,
                    'parameters' => $routeParameters,
                ], $documentation));
            }
            if (in_array('PUT', $rule->verb) && $this->includePut) {
                $path->put = new Put(ArrayHelper::merge([
                    'tags' => $tags,
                    'parameters' => $routeParameters,
                ], $documentation));
            }
            if (in_array('PATCH', $rule->verb)) {
                $path->patch = new Patch(ArrayHelper::merge([
                    'tags' => $tags,
                    'parameters' => $routeParameters,
                ], $documentation));
            }
            if (in_array('DELETE', $rule->verb)) {
                $path->delete = new Delete(ArrayHelper::merge([
                    'tags' => $tags,
                    'parameters' => $routeParameters,
                ], $documentation));
            }
            if (in_array('OPTIONS', $rule->verb) && $this->includeOptions) {
                $path->options = new Options(ArrayHelper::merge([
                    'tags' => $tags,
                    'parameters' => $routeParameters,
                ], $documentation));
            }
            if (in_array('HEAD', $rule->verb) && $this->includeHead) {
                $path->head = new Head(ArrayHelper::merge([
                    'tags' => $tags,
                    'parameters' => $routeParameters,
                ], $documentation));
            }

            $paths[$pathUrl] = $path;
        }
        $openApi->paths = $paths;
    }

    public function getFromCache($version, $key, $cacheClosure)
    {
        if ($this->enableCache) {
            $pathAlias = "@doc/cache/swagger/[$version] $key";
            $path = \Yii::getAlias($pathAlias);
            if (!file_exists($path)) {
                file_put_contents($path, $cacheClosure());
            }

            return file_get_contents($path);
        }

        return $cacheClosure();
    }

    public function toYaml($version = 'latest')
    {
        return $this->fromCache($version, 'yaml');
    }

    public function toJson($version = 'latest')
    {
        return $this->fromCache($version, 'json');
    }

    public function fromCache($version = 'latest', $type = 'yaml')
    {
        return $this->getFromCache($version, "swagger.$type",
            function () use ($version, $type) {
                $this->process($version);
                if ($type === 'json') {
                    return $this->getOpenApi()->toJson();
                } elseif ($type === 'yaml') {
                    return $this->getOpenApi()->toYaml();
                }

                return null;
            });
    }
//
//    public function toJson($version = "latest")
//    {
//        return $this->fromCache($version, "json");
//    }

    public function loadChanges()
    {
        $path = \Yii::getAlias('@api/changes.json');
        if (realpath($path)) {
            $content = file_get_contents(realpath($path));
            $this->_changes = json_decode($content, true);

            return;
        }
        $this->_changes = [];
    }
}
