<?php

namespace api\modules\auth\controllers;

use filsh\yii2\oauth2server\filters\auth\CompositeAuth;
use filsh\yii2\oauth2server\filters\ErrorToExceptionFilter;
use filsh\yii2\oauth2server\Response;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Controller;

class SiteController extends Controller
{
    public $enableCsrfValidation = false;
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'authenticator' => [
                'class' => CompositeAuth::className(),
                'authMethods' => [
                    ['class' => HttpBearerAuth::className()],
                    ['class' => QueryParamAuth::className(), 'tokenParam' => 'accessToken'],
                ],
                "optional" => [
                    "token",
                    "authorize",
                ],
            ],
            'exceptionFilter' => [
                'class' => ErrorToExceptionFilter::className(),
            ],
        ]);
    }

    /**
     * @return mixed
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function actionAuthorize()
    {
//        dd(\Yii::$app->request->get());
        if (\Yii::$app->getUser()->getIsGuest()) {
            return $this->redirect(Url::to(['/auth/default/login-view']));
        }

        /** @var $module \filsh\yii2\oauth2server\Module */
        $module = \Yii::$app->getModule('oauth2');

        $server = $module->getServer();

        /** @var \filsh\yii2\oauth2server\Response $response */
        $response = $module->getServer()->handleAuthorizeRequest(
            null,
            null,
            !\Yii::$app->getUser()->getIsGuest(),
            \Yii::$app->getUser()->getId()
        );

        if ($response->isRedirection()) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;
//            dd($response->getHttpHeader('Location'));
            $response->send();
            return \Yii::$app->response->redirect($response->getHttpHeader('Location'));
        }
        return null;
    }

    /**
     * @return mixed
     */
    public function actionToken()
    {
        /** @var $module \filsh\yii2\oauth2server\Module */
        $module = \Yii::$app->getModule('oauth2');
        /** @var \filsh\yii2\oauth2server\Server $server */
        $server = $module->getServer();
//        dd($server->getConfig());
        $response = $server->handleTokenRequest();

        /* @var $response \filsh\yii2\oauth2server\Response */
        \Yii::$app->getResponse()->format = \yii\web\Response::FORMAT_JSON;

        \Yii::error(json_encode(["body" => json_encode($response->getResponseBody(), true)], JSON_PRETTY_PRINT|JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
        return json_decode($response->getResponseBody(), true);
    }

    /**
     * @return mixed
     */
    public function actionRefreshToken()
    {
        if (\Yii::$app->getUser()->getIsGuest()) {
            return $this->redirect(Url::to(['/auth/default/login-view']));
        }
        /** @var $module \filsh\yii2\oauth2server\Module */
        $module = \Yii::$app->getModule('oauth2');
        $request = \filsh\yii2\oauth2server\Request::createFromGlobals();
        $request->headers = [];
        $request->request['client_id'] = 'yandex.alice';
        $request->request['client_secret'] = 'Uub-SKdsYhSlqDt2feq-fCCtMRPK_DjN';
        $request->request['grant_type'] = 'refresh_token';
        $response = new Response();
        $response = $module->getServer()->grantAccessToken($request, $response);

        /* @var object $response \OAuth2\Response */
        \Yii::$app->getResponse()->format = \yii\web\Response::FORMAT_JSON;

        return $response->getParameters();
    }
}
