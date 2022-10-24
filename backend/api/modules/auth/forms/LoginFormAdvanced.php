<?php

namespace api\modules\auth\forms;

// use api\middleware\PhoneCodeRequired;
use common\components\CheckPhoneCodeService;
use common\components\confirmPhone\ConfirmPhoneService;
use common\contracts\ISwaggerDoc;
use common\exceptions\ValidationException;
use OpenApi\Annotations\OpenApi;
use OpenApi\Annotations\Property;
use OpenApi\Annotations\Schema;
use Yii;
use yii\base\Model;
use yii\web\HttpException;
use yii\web\IdentityInterface;

class LoginFormAdvanced extends Model implements ISwaggerDoc
{
    protected $ip = null;
    protected $code;

    public ?string $username = null;
    public ?string $password = null;
    public $rememberMe = true;

    private $_user;
    /**
     * @var \yii\rbac\Role[]|array
     */
    private $_userRoles = null;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->ip = Yii::$app->request->userIP;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
            [['username'], 'allowedIP', 'when' => function ($rule, $attr) {
                return $this->checkUserRoles($rule, $attr, 'only_allowed_ip');
            }],
            [['username'], 'phoneCodeRequired', 'when' => function ($rule, $attr) {
                return $this->checkUserRoles($rule, $attr, 'required_code');
            }],
//            ["username", 'phoneCodeRequired', "on" => [$this, "checkUserRoles"]]
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param  string  $attribute  the attribute currently being validated
     * @param  array  $params  the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Неверный логин или пароль');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return bool|User whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return $this->getUser();
        }

        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return IdentityInterface|null
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            $user = User::findByUsername($this->username);

            if (!$user) {
                throw new HttpException(403, 'Не удалось авторизовать, проверьте действительность учетных данных.');
            }

            $this->_userRoles = Yii::$app->authManager->getRolesByUser($user->id);
            $this->_user = $user;
        }

        return $this->_user;
    }

    private function ensureCanLoginFromIP(IdentityInterface $user, $ip)
    {
        $allowed = UserAllowedIp::find()
            ->select('value')
            ->andWhere([
                'AND',
                ['user_id' => $user->getId()],
                ['is_active' => 1],
                ['LIKE', 'value', $ip],
            ])
            ->exists();

        if (!$allowed) {
            throw new \Exception('Disallowed IP');
        }
    }

    public function checkUserRoles($rule, $attribute, $param)
    {
        $user = $this->getUser();
        if (!$this->_userRoles) {
            $this->_userRoles = Yii::$app->authManager->getRolesByUser($user->id);
        }

        if ($this->_userRoles) {
            foreach ($this->_userRoles as $role) {
                $value = $role->data[$param] ?? false;
                if ($value) {
                    return true;
                }
            }
        }

        return false;
    }

    public function allowedIP($attribute)
    {
        $this->ensureCanLoginFromIP($this->getUser(), $this->ip);
    }

    public function phoneCodeRequired($attribute)
    {
        $user = $this->getUser();

        /** @var CheckPhoneCodeService $service */
        $service = Yii::$app->get(CheckPhoneCodeService::class);

        $service->ensureIsValidPhoneCode($this->username);

        return true;

        // Проверить заголовки
        if (!Yii::$app->request->headers->get('X-Phone-Send-Type')) {
            throw new ValidationException(['Требуется подтверждение через номер телефон. Укажите вариант: СМС или звонок.']);
        }

        $checker = new ConfirmPhoneService($user);

        if (Yii::$app->request->headers->get('X-Phone-Code')) {
            $result
                = $checker->checkCode(Yii::$app->request->headers->get('X-Phone-Code'));

            return $result;
        }

        if (Yii::$app->request->headers->get('X-Phone-Send-Type')
            == 'sms'
        ) {
            $checker->sendCode();
            throw new ValidationException(['Требуется подтверждение через номер телефона. Вам отправлено СМС.']);
        }

        if (Yii::$app->request->headers->get('X-Phone-Send-Type')
            == 'call'
        ) {
            $checker->sendCode();
            throw new ValidationException(['Требуется подтверждение через номер телефона. Вам отправлен звонок.']);
        }

        return true;
    }

    public static function __makeDocumentationEntity()
    {
        return new static([
            'username' => '79000000000',
            'password' => 'tester',
            'rememberMe' => true,
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
