<?php

namespace api\modules\auth\forms;

use api\modules\auth\models\User;
use api\modules\auth\validators\CheckAllowIPValidator;
use api\modules\auth\validators\PasswordValidator;
use api\modules\auth\validators\TwoFactorValidator;
use api\modules\auth\validators\UsernameValidator;
use common\behaviors\PhoneFormatBehavior;
use common\contracts\ISwaggerDoc;
use common\exceptions\ValidationException;
use OpenApi\Annotations\OpenApi;
use OpenApi\Annotations\Property;
use OpenApi\Annotations\Schema;
use yii\base\Model;

class LoginForm extends Model implements ISwaggerDoc
{
    public const SCENARIO_TWO_FACTOR = 'two_factor';

    protected $code;

    public ?string $username = null;
    public ?string $password = null;
    public ?string $accept_token = null;

    public $rememberMe = true;

    private $_user;
    /**
     * @var \yii\rbac\Role[]|array
     */
    private $_userRoles = null;

    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    public function behaviors()
    {
        return [
            [
                'class' => PhoneFormatBehavior::class,
                'attribute' => 'username',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'password', 'accept_token'], 'safe'],
            [['username', 'password'], 'required'],
            [
                ['username'],
                UsernameValidator::class,
            ],
            [
                ['password'],
                PasswordValidator::class,
            ],
            [
                ['username'],
                CheckAllowIPValidator::class,
            ],
            [
                ['accept_token'],
                TwoFactorValidator::class,
            ],
        ];
    }

    public function login()
    {
        if (!$this->validate()) {
            throw new ValidationException($this->errors);
        }

        return User::findByUsername($this->username);
    }

    public static function __makeDocumentationEntity()
    {
        return new static([
            'username' => '79000000000',
            'password' => 'tester',
            //            "rememberMe" => true,
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
