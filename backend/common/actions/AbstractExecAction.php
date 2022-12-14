<?php

namespace common\actions;

use common\contracts\ISwaggerDoc;
use common\modules\permission\AccessControl;
use common\modules\permission\AccessRule;
use doc\DocumentationHelper;
use OpenApi\Annotations\OpenApi;
use OpenApi\Annotations\Property;
use OpenApi\Annotations\Schema;
use Redbox\JsonRpc\Actions\ExecAction;
use yii\base\ActionEvent;
use yii\base\Controller;
use yii\web\UploadedFile;

abstract class AbstractExecAction extends ExecAction implements ISwaggerDoc
{
    protected function beforeRun()
    {
        $event = new ActionEvent($this);
        $this->trigger(Controller::EVENT_BEFORE_ACTION, $event);
        return parent::beforeRun(); // TODO: Change the autogenerated stub
    }


    public function behaviors()
    {
        return [
            "access" => [
                "class" => AccessControl::class,
                "rules" => [
                    [
                        "class" => AccessRule::class,
                        "allow" => true,
                        "enforce" => [
                            "act" => "allow",
                            "model" => "Action",
                            "attr" => "run",
                        ]
                    ]
                ],
            ]
        ];
    }

    public static function __docAttributeIgnore()
    {
        return [];
    }

    public static function __docAttributeExample()
    {
        return [];
    }

    public static function __makeDocumentationEntity()
    {
        return [];
    }

    public function __docs(OpenApi $openApi)
    {
        $reflect = new \ReflectionClass($this);
        $schema = new Schema([
            'schema' => $reflect->getShortName(),
            'title' => $reflect->getShortName(),
            'properties' => [],
        ]);

        $types = [];

        if (method_exists($this, '__docAttributeTypes')) {
            $types = $this->__docAttributeTypes();
        }

        $fields = [
            'id',
        ];

        foreach ($fields as $field) {
            if ($field === null) {
                continue;
            }
            if (!in_array($field, $this->__docAttributeIgnore())) {
                $propertyData = [
                    'property' => $field,
                    'title' => 'example',
                    'description' => $field,
                    'type' => 'string',
                ];

                if (isset($types[$field])) {
                    $type = $types[$field];

                    if ($type == UploadedFile::class) {
                        $type = 'file';
                    }

                    $propertyData['type'] = $type;
                }

                $prop = new Property($propertyData);
                $prop->example = DocumentationHelper::getExampleValue($field, $this->__docAttributeExample());
                if (is_numeric($prop->example)) {
                    $prop->type = 'integer';
                }
                $schema->properties[] = $prop;
            }
        }

        return $schema;
    }
}
