<?php

namespace common;

use api\Request;
use common\actions\OptionsAction;
use doc\DocApplication;
use filsh\yii2\oauth2server\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\Cors;
use yii\rest\Controller;
use yii\web\Response;

/**
 * @property Request $request
 *
 * @package common
 */
abstract class AbstractController extends Controller
{
    protected $accessAnyDomains = true;

    public function allowedDomains()
    {
        $accessDomains = [
            env('API_URL'),
            env('FRONTEND_URL'),
            env('FRONTEND_SYSTEM_URL'),
        ];

        if (\Yii::$app->request->getHeaders()->has('Origin')
            && $this->accessAnyDomains
        ) {
            $accessDomains[] = \Yii::$app->request->getHeaders()
                ->get('Origin', 'http://localhost:8000');
        }

        return $accessDomains;
    }

    public function allowedHeaders()
    {
        return ['content-type', 'authorization'];
    }

    /**
     * Отключение CORS
     * Отключение проверки авторизации на [OPTIONS] запросах.
     *
     * @return array|array[]
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => $this->allowedDomains(),
                'Access-Control-Request-Method' => [
                    'GET',
                    'POST',
                    'PUT',
                    'PATCH',
                    'DELETE',
                    'HEAD',
                    'OPTIONS',
                ],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Allow-Headers' => $this->allowedHeaders(),
                'Access-Control-Request-Headers' => ['*'],
            ],
        ];

        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [
                [
                    'class' => \yii\filters\auth\HttpBasicAuth::class,
                    'realm' => 'api',
                ],
                ['class' => HttpBearerAuth::class],
                [
                    'class' => QueryParamAuth::class,
                    'tokenParam' => 'access-token',
                ],
            ],
            'except' => ['options'],
        ];

        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return array_merge(parent::actions(), [
            'options' => [
                'class' => OptionsAction::class,
            ],
        ]);
    }

    public function runAction($id, $params = [])
    {
        if (\Yii::$app instanceof DocApplication
            && !$this->request->isOptions
            && !$this->request->isHead
        ) {
            $controllerClass = get_class($this);
            $isAbstractActiveController = $this instanceof AbstractActiveController;
            $isMethodExist = method_exists($this, '__examples');

            if ($isAbstractActiveController && $isMethodExist) {
                $result = $this->__examples($id);
                if (is_array($result) || is_object($result)) {
                    $this->response->format = Response::FORMAT_JSON;

                    return $result;
                }
                $this->response->format = Response::FORMAT_RAW;

                return null;
            } elseif ($this instanceof AbstractActiveController) {
                return [
                    "Не задан пример! Укажите в контроллере метод $controllerClass::__examples(\$action) : ?array",
                ];
            }
        }

        $token = \Yii::t('app', '(action:{id}) params:{params})', [
            'id' => $id,
            'params' => json_encode($params),
        ]);
        \Yii::beginProfile($token, __METHOD__);
        $result = parent::runAction($id, $params);
        \Yii::endProfile($token, __METHOD__);

        return $result;
    }
}
