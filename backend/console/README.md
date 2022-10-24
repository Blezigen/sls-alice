# Новый функционал

1. Добавлена возможность создать документацию автоматически, на основании модели из базы данных. 
2. Переделан генератор модели
3. Добавлена авторизация

## Документирование

Для документирования Api был сделан отдельный контроллер который в реальном времени формирует api.
Для его грамотной работы все роуты должны быть перенесены внутрь модулей.

>Module.php
>```php 
>class Module extends \common\AbstractModule
>{
>    // other code
>    
>    public function routes($moduleID)
>    {
>        $moduleID = $this->id;
>        return [
>            //<editor-fold desc="modules">
>            "PUT,PATCH modules/<id:\d>" => "system/module/update",
>            "DELETE modules/<id:\d>"    => "system/module/delete",
>            "GET,HEAD modules/<id:\d>"  => "system/module/view",
>            "POST modules"              => "system/module/create",
>            "GET,HEAD modules"          => "system/module/index",
>            "modules/<id:\d>"           => "system/module/options",
>            "modules"                   => "system/module/options",
>            //</editor-fold>
>
>        ];
>    }
>}
>```

Так-же необходимо добавлять модуль в две части в файле main.php, это позволит инициализировать маршруты.

>main.php 
>```php
>    // other code
>    'bootstrap'           => [
>        "module",
>    ],
>    // other code
>    'modules'             => [
>        'module' => [
>            'class' => \api\modules\moduleNamespace\Module::class,
>        ],
>    ],
>
>```

Что-бы в документации появились маршруты необходимо указать контроллеру какие модули поддаются документированию.

>SwaggerController.php
>```php
>class SwaggerController extends Controller
>{
>    public function modules()
>    {
>        return [
>            "auth",
>            "region",
>            "profile",
>            "system",
>        ];
>    }
>    
>    // other code
>}
>```

Так-же необходимо в связанный контроллер добавить информацию для генерации доки:
>DefaultController.php
>```php
>    public function __docs($pathUrl, $action)
>    {
>        switch ($action){
>            //<editor-fold desc="action">
>            case "action":
>                return [
>                    "summary" => "Короткое описание",
>                    "description" => "Полное описание",
>                    "security" => [
>                        SwaggerHelper::securityBearerAuth(), 
>                        SwaggerHelper::securityOAuth(), 
>                        SwaggerHelper::securityAccessToken()
>                    ],
>                    "parameters"  => [
>                        SwaggerHelper::filterPropertyFromClass("Фильтрация",ModelWrite::class),
>                        SwaggerHelper::extraPropertyFromClass("Extend",ModelWrite::class),
>                        SwaggerHelper::headerProperty("Header-Name","Описание заголовка"),
>                        SwaggerHelper::sortProperty("Сортировка", $this->sorts()),
>                    ],
>                    "requestBody" => (new SwaggerRequestBuilder([]))->json(ModelWrite::class, true)->generate(),
>                    "responses"   => [
>                        "200" => (new \common\helpers\SwaggerResponseBuilder([
>                            "pathUrl"     => $pathUrl,
>                            "statusCode"  => 200,
>                            "description" => "OK",
>                            "pagination" => false,
>                        ]))->json(ModelRead::class)->generate(),
>                        "404" => (new SwaggerExceptionResponseBuilder(NotFoundHttpException::class))->generate(),
>                    ]
>                ];
>            //</editor-fold>
>        }
>    }
>```


## Генератор модуля
Просто попробуй
```bash  
php yii generator/default/module --moduleID="moduleName" --ns='api\modules' --tableName=users,partners
```

## Генератор мета таблицы
Просто попробуй
```bash  
php yii generator/default/meta --tableName=regions
```

## Генератор модели
Просто попробуй
```bash  
php yii generator/default/model --tableName="users"
```