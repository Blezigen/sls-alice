<?php

namespace api\modules\auth\controllers;

use api\exceptions\NotFoundHttpException;
use api\modules\auth\actions\LoginAction;
use api\modules\auth\actions\RecoveryAction;
use api\modules\auth\actions\RefreshAction;
use api\modules\auth\actions\RevokeAction;
use api\modules\auth\forms\LoginForm;
use api\modules\auth\forms\RecoveryForm;
use api\modules\auth\forms\RefreshForm;
use api\modules\auth\forms\RevokeForm;
use api\modules\auth\models\JwtAuthResult;
use common\AbstractController;
use common\helpers\SwaggerExceptionResponseBuilder;
use common\helpers\SwaggerHelper;
use common\helpers\SwaggerRequestBuilder;
use common\helpers\SwaggerResponseBuilder;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;

class JwtController extends AbstractController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [
                HttpBearerAuth::class,
                QueryParamAuth::class,
            ],
            'except' => ['login', 'refresh', 'recovery', 'revoke'],
            'optional' => ['options'],
        ];

        return $behaviors;
    }

    public function actions()
    {
        return array_merge(parent::actions(), [
            'login' => [
                'class' => LoginAction::class,
            ],
            'refresh' => [
                'class' => RefreshAction::class,
            ],
            'revoke' => [
                'class' => RevokeAction::class,
            ],
            'recovery' => [
                'class' => RecoveryAction::class,
            ],
        ]);
    }

    public function __docs($pathUrl, $action)
    {
        switch ($action) {
            case 'login':
                return [
                    // 'tags' => ['Авторизация'],
                    'summary' => 'Получить Access JWT-токен',
                    'security' => [
                        SwaggerHelper::securityBearerAuth(),
                        SwaggerHelper::securityOAuth(),
                        SwaggerHelper::securityAccessToken(),
                    ],
                    'requestBody' => (new SwaggerRequestBuilder([
                        'description' => 'OK',
                    ]))->json(LoginForm::class, true)->generate(),
                    'responses' => [
                        '200' => (new SwaggerResponseBuilder([
                            'pathUrl' => $pathUrl,
                            'statusCode' => 200,
                            'description' => 'OK',
                        ]))->json(JwtAuthResult::class, true)->generate(),
                        '404' => (new SwaggerExceptionResponseBuilder(NotFoundHttpException::class))->generate(),
                    ],
                ];
            case 'refresh':
                return [
                    // 'tags' => ['Авторизация'],
                    'summary' => 'Получение нового токена используя refresh-token',
                    'security' => [
                        SwaggerHelper::securityBearerAuth(),
                        SwaggerHelper::securityOAuth(),
                        SwaggerHelper::securityAccessToken(),
                    ],
                    'requestBody' => (new SwaggerRequestBuilder([
                        'description' => 'OK',
                    ]))->json(RefreshForm::class, true)->generate(),
                    'responses' => [
                        '200' => (new SwaggerResponseBuilder([
                            'pathUrl' => $pathUrl,
                            'statusCode' => 200,
                            'description' => 'OK',
                        ]))->json(JwtAuthResult::class, true)->generate(),
                        '404' => (new SwaggerExceptionResponseBuilder(NotFoundHttpException::class))->generate(),
                    ],
                ];
            case 'revoke':
                return [
                    // 'tags' => ['Авторизация'],
                    'summary' => 'Освободить токен',
                    'security' => [
                        SwaggerHelper::securityBearerAuth(),
                        SwaggerHelper::securityOAuth(),
                        SwaggerHelper::securityAccessToken(),
                    ],
                    'requestBody' => (new SwaggerRequestBuilder([
                        'description' => 'OK',
                    ]))->json(RevokeForm::class, true)->generate(),
                    'responses' => [
                        '200' => (new SwaggerResponseBuilder([
                            'pathUrl' => $pathUrl,
                            'statusCode' => 200,
                            'description' => 'OK',
                        ]))->json(JwtAuthResult::class, true)->generate(),
                        '404' => (new SwaggerExceptionResponseBuilder(NotFoundHttpException::class))->generate(),
                    ],
                ];
            case 'recovery':
                return [
                    // 'tags' => ['Авторизация'],
                    'summary' => 'Восстановление доступа',
                    'security' => [
                        SwaggerHelper::securityBearerAuth(),
                        SwaggerHelper::securityOAuth(),
                        SwaggerHelper::securityAccessToken(),
                    ],
                    'requestBody' => (new SwaggerRequestBuilder([
                        'description' => 'OK',
                    ]))->json(RecoveryForm::class, true)->generate(),
                    'responses' => [
                        '200' => (new SwaggerResponseBuilder([
                            'pathUrl' => $pathUrl,
                            'statusCode' => 200,
                            'description' => 'OK',
                        ]))->json(JwtAuthResult::class, true)->generate(),
                        '404' => (new SwaggerExceptionResponseBuilder(NotFoundHttpException::class))->generate(),
                    ],
                ];
        }

        return [];
    }

    public function __examples($action)
    {
        switch ($action) {
            case 'login':
                return JwtAuthResult::__makeDocumentationEntity();
        }

        throw new \Exception();
    }
}
