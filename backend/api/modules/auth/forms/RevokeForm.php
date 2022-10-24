<?php

namespace api\modules\auth\forms;

use common\BaseModel;
use OpenApi\Annotations\OpenApi;
use OpenApi\Annotations\Property;
use OpenApi\Annotations\Schema;

class RevokeForm extends BaseModel
{
    public $token = null;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['token'], 'required'],
        ];
    }

    public function revoke()
    {
        /** @var $module \filsh\yii2\oauth2server\Module */
        $module = \Yii::$app->getModule('oauth2');
        $request = \filsh\yii2\oauth2server\Request::createFromGlobals();

        $response = $module->getServer()->handleRevokeRequest(
            $request
        );

        if ($response->getParameter('revoke') === true) {
            return true;
        }

        return $response->getParameters();
    }

    public static function __makeDocumentationEntity()
    {
        return new static([
            'token' => '8d3ba6e9ef3000db2c25faae9fc3bebba5c37d24',
        ]);
    }

    public function __docs(OpenApi $openApi)
    {
        $reflect = new \ReflectionClass($this);
        $schema = new Schema([
            'schema' => $reflect->getShortName(),
            'title' => $reflect->getShortName(),
            'properties' => [],
        ]);

        foreach ($this->attributes as $attribute => $value) {
            $schema->properties[] = new Property([
                'property' => $attribute,
                'title' => $attribute,
                'description' => $attribute,
                'example' => $this->$attribute,
            ]);
        }

        foreach (array_keys($this->extraFields()) as $attribute) {
            $schema->properties[] = new Property([
                'property' => $attribute,
                'title' => $attribute,
                'description' => $attribute,
            ]);
        }

        return $schema;
    }

    public static function __docAttributeIgnore()
    {
        return [];
    }

    public static function __docAttributeExample()
    {
        return [];
    }
}
