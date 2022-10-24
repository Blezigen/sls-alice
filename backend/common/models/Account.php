<?php

namespace common\models;

use common\IConstant;
use filsh\yii2\oauth2server\Response;
use OAuth2\Storage\UserCredentialsInterface;
use Yii;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "{{%accounts}}".
 *
 * @property Collection $status
 * @property Session $createdSes
 * @property Session $updatedSes
 * @property Session $deletedSes
 * @property Contractor $contractor
 * @property string $role
 * @property Session[] $sessions
 */
class Account extends \common\database\Account implements IdentityInterface, UserCredentialsInterface
{
    private $_role = null;

    public function getSetting($section, $key, $default = null)
    {
        if (!$this->contractor) {
            return Yii::$app->settings->getRoleSettings(
                [$this->role],
                $section,
                $key,
                $default
            );
        }

        return $this->contractor->getSetting(
            $section,
            $key,
            $default
        );
    }

    public function setSetting($section, $key, $value)
    {
        if (!$this->contractor) {
            return;
        }

        $this->contractor->setSetting(
            $section,
            $key,
            $value,
        );
    }

    public function getRole()
    {
        if (!$this->id) {
            return $this->_role;
        }
        $roles = Yii::$app->permission->getRolesForUser($this->id);
        if (empty($roles)) {
            return 'guest';
        }

        return $roles[array_key_first($roles)];
    }

    public function setRole($role)
    {
        $this->_role = $role;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['username'], 'unique'],
            [['role'], 'required'],
        ]);
    }

    public function fields()
    {
        $fields = parent::fields();
        unset($fields['password_hash']);
        unset($fields['password_reset_token']);
        unset($fields['auth_key']);
        unset($fields['deleted_at']);
        unset($fields['deleted_acc']);

        return array_merge($fields, []);
    }

    /**
     * {@inheritdoc}
     */
    public function extraFields()
    {
        return array_merge(parent::extraFields(), [
            'role' => function () {
                return $this->role;
            },
            'status' => function () {
                return $this->status;
            },
            'created_acc' => function () {
                return $this->createdSes;
            },
            'updated_acc' => function () {
                return $this->updatedSes;
            },
            'deleted_acc' => function () {
                return $this->deletedSes;
            },
            'contractor' => function () {
                return $this->contractor;
            },
            'sessions' => function () {
                return $this->sessions;
            },
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            // 'id' => 'ID',
            // 'status_cid' => 'Status Cid',
            // 'username' => 'Username',
            // 'password_hash' => 'Password Hash',
            // 'password_reset_token' => 'Password Reset Token',
            // 'auth_key' => 'Auth Key',
            // 'last_login_at' => 'Last Login At',
            // 'created_acc' => 'Created Ses',
            // 'created_at' => 'Created At',
            // 'updated_acc' => 'Updated Ses',
            // 'updated_at' => 'Updated At',
            // 'deleted_at' => 'Deleted At',
            // 'deleted_acc' => 'Deleted Ses',
        ]);
    }

    public static function __docAttributeExample()
    {
        $faker = \Faker\Factory::create();

        return array_merge(parent::__docAttributeExample(), [
            'username' => $faker->userName,
        ]);
    }

    /**
     * Gets query for [[Status]].
     *
     * @return \yii\db\ActiveQuery|\common\queries\CollectionQuery
     */
    public function getStatus()
    {
        return $this->hasOne(Collection::className(), ['id' => 'status_cid']);
    }

    /**
     * Gets query for [[CreatedSes]].
     *
     * @return \yii\db\ActiveQuery|\common\queries\SessionQuery
     */
    public function getCreatedSes()
    {
        return $this->hasOne(Session::className(), ['id' => 'created_acc']);
    }

    /**
     * Gets query for [[UpdatedSes]].
     *
     * @return \yii\db\ActiveQuery|\common\queries\SessionQuery
     */
    public function getUpdatedSes()
    {
        return $this->hasOne(Session::className(), ['id' => 'updated_acc']);
    }

    /**
     * Gets query for [[DeletedSes]].
     *
     * @return \yii\db\ActiveQuery|\common\queries\SessionQuery
     */
    public function getDeletedSes()
    {
        return $this->hasOne(Session::className(), ['id' => 'deleted_acc']);
    }

    /**
     * Gets query for [[Contractor]].
     *
     * @return \yii\db\ActiveQuery|\common\queries\ContractorQuery
     */
    public function getContractor()
    {
        return $this->hasOne(Contractor::className(), ['account_id' => 'id']);
    }

    /**
     * Gets query for [[Sessions]].
     *
     * @return \yii\db\ActiveQuery|\common\queries\SessionQuery
     */
    public function getSessions()
    {
        return $this->hasMany(Session::className(), ['account_id' => 'id']);
    }

//    public static function __makeDocumentationEntity()
//    {
//        return new self([
//            "id" => "",
//            "status_cid" => "",
//            "username" => "",
//            "password_hash" => "",
//            "password_reset_token" => "",
//            "auth_key" => "",
//            "last_login_at" => "",
//            "created_acc" => "",
//            "created_at" => "",
//            "updated_acc" => "",
//            "updated_at" => "",
//            "deleted_at" => "",
//            "deleted_acc" => "",
//        ]);
//    }

    public static function findByUsername($username)
    {
        $entityQuery = static::find()->andWhere([
            'username' => $username,
            /* 'status_id' => self::STATUS_ACTIVE */
        ]);

//        dd($entityQuery->createCommand()->rawSql);

        return $entityQuery->one();
    }

    public static function findIdentity($id)
    {
        return static::findOne([
            'id' => $id,
            /* 'status_id' => self::STATUS_ACTIVE */
        ]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        /** @var $module \filsh\yii2\oauth2server\Module */
        $module = \Yii::$app->getModule('oauth2');

        if ($module->getServer()->verifyResourceRequest()) {
            $request = \filsh\yii2\oauth2server\Request::createFromGlobals();
            $response = new Response();
            if ($response->getParameter('error') === null) {
                $token = $module->getServer()
                    ->getAccessTokenData($request, $response);

                return Account::findIdentity($token['user_id']);
            }
        }
        throw new \yii\web\NotFoundHttpException('Пользователь не найден');
    }

    public function getId()
    {
        return $this->getPrimaryKey();
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password,
            $this->password_hash);
    }

    public function setPassword($password)
    {
        $this->password_hash
            = Yii::$app->security->generatePasswordHash($password);
    }

    public function generatePasswordResetToken()
    {
        $this->password_reset_token
            = Yii::$app->security->generateRandomString() . '_' . time();
    }

    public function generatePasswordResetTokenByCode($code)
    {
        $this->password_reset_token = md5($code) . '_' . time();
    }

    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    public function checkUserCredentials($username, $password)
    {
        $user = self::findByUsername($username);
        if (!$user) {
            return false;
        }

        return $user->validatePassword($password);
    }

    public function getUserDetails($username)
    {
        $user = Account::find()->select('id')
            ->andWhere(['username' => $username])->one();

        return $user->toArray();
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($this->_role) {
            Yii::$app->permission->deleteRolesForUser($this->id);
            Yii::$app->permission->addRoleForUser($this->id, $this->_role);
        }
    }

    public function getReserves()
    {
        return $this->hasMany(Order::class, ['created_acc' => 'id'])
            ->select(['orders.id', 'orders.created_acc'])
            ->joinWith(['orderType' => function ($q) {
                $q->andWhere(['slug' => IConstant::ORDER_TYPE_TEMP]);
            }]);
    }

    public function getReceipts()
    {
        return $this->hasMany(Order::class, ['created_acc' => 'id'])
            ->select(['orders.id', 'orders.created_acc'])
            ->joinWith(['orderType' => function ($q) {
                $q->andWhere(['slug' => IConstant::ORDER_TYPE_GENERAL]);
            }, 'paymentStatus' => function ($q) {
                $q->andWhere(['<>', 'slug', IConstant::PAYMENT_STATUS_NOT_PAYED]);
            }]);
    }

    public function getCabins()
    {
        return $this->hasMany(Order::class, ['created_acc' => 'id'])
            ->select(['orders.id', 'orders.created_acc'])
            ->joinWith([
                'orderCabins' => function ($q) {
                },
                'paymentStatus' => function ($q) {
                    $q->andWhere([
                        'OR',
                        ['<>', 'slug', IConstant::PAYMENT_STATUS_NOT_PAYED],
                        ['is', 'slug', null],
                    ]);
                }, ]);
    }

    public function getReserveCount()
    {
        return $this->getReserves()->count();
    }

    public function getReceiptCount()
    {
        return $this->getReceipts()->count();
    }

    public function getCabinCount($add = 0)
    {
        return $this->getCabins()->noCache()->count() + $add;
    }
}
