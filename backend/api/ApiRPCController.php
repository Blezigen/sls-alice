<?php

namespace api;

use common\helpers\SwaggerHelper;
use common\helpers\SwaggerRequestBuilder;
use filsh\yii2\oauth2server\filters\auth\CompositeAuth;
use Redbox\JsonRpc\Controller;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;

class ApiRPCController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [
                ['class' => HttpBearerAuth::class],
                ['class' => QueryParamAuth::class, 'tokenParam' => 'access-token'],
            ],
            'except' => ['options'],
            'optional' => ['*'],
        ];

        return $behaviors;
    }

    public function __docs($pathUrl, $action)
    {
        $actions = $this->actions();
        unset($actions['options']);

        $builder = (new SwaggerRequestBuilder([]));

        $data = $builder->getRpcFunctions($actions);

        $description = $this->renderPartial('@doc/../views/rpc_table', ['methods' => $data]);
        switch ($action) {
            // <editor-fold desc="attach">
            case '':
                return [
                    // 'tags' => ['exec'],
                    'summary' => 'Вызов удалённых процедур',
                    'description' => $description,
                    'security' => [SwaggerHelper::securityBearerAuth(), SwaggerHelper::securityOAuth(), SwaggerHelper::securityAccessToken()],
                    'parameters' => [],
                    'requestBody' => $builder->rpc($this, $actions)->generate(),
                    'responses' => [
//                        "200" => (new \common\helpers\SwaggerResponseBuilder([
//                            "pathUrl"     => $pathUrl,
//                            "statusCode"  => 200,
//                            "description" => "OK",
//                        ]))->json(UserRead::class)->generate(),
//                        "404" => (new SwaggerExceptionResponseBuilder(NotFoundHttpException::class))->generate(),
                    ],
                ];
            // </editor-fold>
        }

        return [];
    }
}
