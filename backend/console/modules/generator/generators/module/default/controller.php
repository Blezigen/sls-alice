<?php
/**
 * This is the template for generating a controller class within a module.
 */

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\module\Generator */
/* @var string $className */
/* @var string $moduleID */
/* @var string $tableName */
/* @var string $ns */

$relation_imploded = '';
if (!empty($relations)) {
    $temp = [];

    foreach ($relations as $name => $relation) {
        $temp[] = '"' . \yii\helpers\Inflector::camel2id($name, '_', true) . '"';
    }
    $relation_imploded = implode(',', $temp);
}

echo "<?php\n";
?>

namespace <?php echo "$ns\\$moduleID\\controllers"; ?>;

use api\exceptions\NotFoundHttpException;
use common\exceptions\ValidationException;
use common\helpers\SwaggerExceptionResponseBuilder;
use common\helpers\SwaggerHelper;
use common\helpers\SwaggerRequestBuilder;
use common\helpers\SwaggerResponseBuilder;
use yii\db\ActiveRecord;
use <?php echo $ns; ?>\<?php echo $moduleID; ?>\models\read\<?php echo $className; ?>Read;
use <?php echo $ns; ?>\<?php echo $moduleID; ?>\models\write\<?php echo $className; ?>Write;
use doc\ExampleHelper;

class <?php echo $className; ?>Controller extends \api\ApiActiveController
{
    /** @var string|ActiveRecord|<?php echo $className; ?>Read */
    public $readModelClass = <?php echo $className; ?>Read::class;
    /** @var string|ActiveRecord|<?php echo $className; ?>Write */
    public $writeModelClass = <?php echo $className; ?>Write::class;

    public function __docs($pathUrl, $action)
    {
        switch ($action){
            //<editor-fold desc="index">
            case "index":
                return [
                    "tags" => ["поиск"],
                    "summary" => "Получить все сущности \"<?php echo $tableName; ?>\"",
                    "description" => "",
                    "security" => [
                        SwaggerHelper::securityBearerAuth(),
                        SwaggerHelper::securityOAuth(),
                        SwaggerHelper::securityAccessToken()
                    ],
                    "parameters"  => [
                        SwaggerHelper::filterPropertyFromClass("Фильтрация",<?php echo $className; ?>Write::class),
                        SwaggerHelper::sortProperty("Сортировка", $this->sorts()),
                        SwaggerHelper::extraPropertyFromClass("Расширение выборки",<?php echo $className; ?>Write::class),
                    ],
                    "responses"   => [
                        "200" => (new SwaggerResponseBuilder([
                            "pathUrl"     => $pathUrl,
                            "statusCode"  => 200,
                            "description" => "OK",
                            "pagination" => true
                        ]))->json(<?php echo $className; ?>Read::class, false)->generate(),
                        "404" => (new SwaggerExceptionResponseBuilder(NotFoundHttpException::class))->generate(),
                    ]
                ];
            //</editor-fold>
            //<editor-fold desc="update">
            case "update":
                return [
                    "tags" => ["изменение", "обновление"],
                    "summary" => "Частичное обновление сущности \"<?php echo $tableName; ?>\"",
                    "description" => "",
                    "security" => [
                        SwaggerHelper::securityBearerAuth(),
                        SwaggerHelper::securityOAuth(),
                        SwaggerHelper::securityAccessToken()
                    ],
                    "parameters"  => [
                        SwaggerHelper::extraPropertyFromClass("Расширение выборки",<?php echo $className; ?>Write::class)
                    ],
                    "requestBody" => (new SwaggerRequestBuilder([]))->json(<?php echo $className; ?>Write::class, true)->generate(),
                    "responses"   => [
                        "200" => (new SwaggerResponseBuilder([
                            "pathUrl"     => $pathUrl,
                            "statusCode"  => 200,
                            "description" => "OK",
                        ]))->json(<?php echo $className; ?>Read::class)->generate(),
                        "404" => (new SwaggerExceptionResponseBuilder(NotFoundHttpException::class))->generate(),
                        "422" => (new SwaggerExceptionResponseBuilder(ValidationException::class))->generate(),
                    ]
                ];
            //</editor-fold>
            //<editor-fold desc="delete">
            case "delete":
                return [
                    "tags" => ["удаление"],
                    "summary" => "Удаление сущности \"<?php echo $tableName; ?>\"",
                    "description" => "",
                    "security" => [
                        SwaggerHelper::securityBearerAuth(),
                        SwaggerHelper::securityOAuth(),
                        SwaggerHelper::securityAccessToken()
                    ],
                    "parameters"  => [],
                    "responses"   => [
                        "204" => (new SwaggerResponseBuilder([
                            "pathUrl"     => $pathUrl,
                            "statusCode"  => 204,
                            "description" => "No Content",
                        ]))->generate(),
                        "404" => (new SwaggerExceptionResponseBuilder(NotFoundHttpException::class))->generate(),
                    ]
                ];
            //</editor-fold>
            //<editor-fold desc="view">
            case "view":
                return [
                    "tags" => ["просмотр"],
                    "summary" => "Получение конкретной сущности \"<?php echo $tableName; ?>\"",
                    "description" => "",
                    "security" => [
                        SwaggerHelper::securityBearerAuth(),
                        SwaggerHelper::securityOAuth(),
                        SwaggerHelper::securityAccessToken()
                    ],
                    "parameters"  => [
                        SwaggerHelper::extraPropertyFromClass("Extend",<?php echo $className; ?>Write::class)
                    ],
                    "responses"   => [
                        "200" => (new SwaggerResponseBuilder([
                            "pathUrl"     => $pathUrl,
                            "statusCode"  => 200,
                            "description" => "OK",
                        ]))->json(<?php echo $className; ?>Read::class)->generate(),
                        "404" => (new SwaggerExceptionResponseBuilder(NotFoundHttpException::class))->generate(),
                    ]
                ];
            //</editor-fold>
            //<editor-fold desc="create">
            case "create":
                return [
                    // "tags" => ["создать", "добавить"],
                    "summary" => "Создание сущности \"<?php echo $tableName; ?>\"",
                    "description" => "",
                    "security" => [
                        SwaggerHelper::securityBearerAuth(),
                        SwaggerHelper::securityOAuth(),
                        SwaggerHelper::securityAccessToken()
                    ],
                    "parameters"  => [
                        SwaggerHelper::extraPropertyFromClass("Extend",<?php echo $className; ?>Write::class)
                    ],
                    "requestBody" => (new SwaggerRequestBuilder([
                        "description" => "OK",
                    ]))->json(<?php echo $className; ?>Write::class, true)->generate(),
                    "responses"   => [
                        "200" => (new SwaggerResponseBuilder([
                            "pathUrl"     => $pathUrl,
                            "statusCode"  => 200,
                            "description" => "OK",
                        ]))->json(<?php echo $className; ?>Read::class)->generate(),
                        "404" => (new SwaggerExceptionResponseBuilder(NotFoundHttpException::class))->generate(),
                        "422" => (new SwaggerExceptionResponseBuilder(ValidationException::class))->generate(),
                    ]
                ];
            //</editor-fold>
        }

        return [];
    }

    public function __examples($action)
    {
        switch ($action){
            case "index":
                return ExampleHelper::paginator(<?php echo $className; ?>Read::class);
            case "delete":
                return null;
            case "update":
            case "view":
            case "create":
                return ExampleHelper::data(<?php echo $className; ?>Read::class);
        }
        return null;
    }
}