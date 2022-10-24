<?php

namespace api\modules\auth\forms;

use common\BaseModel;
use filsh\yii2\oauth2server\Response;
use OpenApi\Annotations\OpenApi;
use OpenApi\Annotations\Property;
use OpenApi\Annotations\Schema;

class RefreshForm extends BaseModel
{
    public $refresh_token = null;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['refresh_token'], 'required'],
        ];
    }

    public function refresh()
    {
        /** @var $module \filsh\yii2\oauth2server\Module */
        $module = \Yii::$app->getModule('oauth2');
        $request = \filsh\yii2\oauth2server\Request::createFromGlobals();
        $request->headers = [];
        $request->request['client_id'] = 'self';
        $request->request['client_secret'] = 'self';
        $request->request['grant_type'] = 'refresh_token';
        $response = new Response();

        $data = $module->getServer()->grantAccessToken($request, $response);

        if ($response->getParameter('error')) {
            throw new \Exception($response->getParameter('error_description'));
        }

        return $data;
    }

    public static function __makeDocumentationEntity()
    {
        return new static([
            'refresh_token' => '8d3ba6e9ef3000db2c25faae9fc3bebba5c37d24',
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
