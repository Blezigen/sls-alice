<?php

namespace api\modules\auth\models;

use common\contracts\ISwaggerDoc;
use OpenApi\Annotations\OpenApi;
use OpenApi\Annotations\Property;
use OpenApi\Annotations\Schema;
use yii\base\Model;
use yii\web\IdentityInterface;

class JwtAuthResult extends Model implements ISwaggerDoc
{
    public $access_token = null;
    public $token_type = 'bearer';
    public $expires_in = 36000;

    public $scope = null;
    public $refresh_token = null;

    public static function make(IdentityInterface $identity)
    {
        /** @var $module \filsh\yii2\oauth2server\Module */
        $module = \Yii::$app->getModule('oauth2');

        $response = $module->getServer()->createAccessToken(
            'self',
            $identity->getId(),
            null,
            true
        );

        return new static($response);
    }

    public static function __makeDocumentationEntity()
    {
        return new static([
            'access_token' => '{{%access_token}}',
            'token_type' => 'bearer',
            'expires_in' => 86400,
            'scope' => null,
            'refresh_token' => '{{%refresh_token}}',
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
