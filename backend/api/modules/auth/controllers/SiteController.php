<?php

namespace api\modules\auth\controllers;

use filsh\yii2\oauth2server\Response;
use yii\helpers\Url;
use yii\web\Controller;

class SiteController extends Controller
{
    /**
     * @return mixed
     */
    public function actionAuthorize()
    {
        if (\Yii::$app->getUser()->getIsGuest()) {
            return $this->redirect(Url::to(['/auth/default/login-view']));
        }

        /** @var $module \filsh\yii2\oauth2server\Module */
        $module = \Yii::$app->getModule('oauth2');
        $response = $module->getServer()->handleAuthorizeRequest(null, null,
            !\Yii::$app->getUser()->getIsGuest(), \Yii::$app->getUser()->getId());

        /* @var object $response \OAuth2\Response */
        \Yii::$app->getResponse()->format = \yii\web\Response::FORMAT_JSON;

        return $response->getParameters();
    }

    /**
     * @return mixed
     */
    public function actionToken()
    {
        if (\Yii::$app->getUser()->getIsGuest()) {
            return $this->redirect(Url::to(['/auth/default/login-view']));
        }
        /** @var $module \filsh\yii2\oauth2server\Module */
        $module = \Yii::$app->getModule('oauth2');
        $response = $module->getServer()->handleTokenRequest();

        /* @var object $response \OAuth2\Response */
        \Yii::$app->getResponse()->format = \yii\web\Response::FORMAT_JSON;

        return $response->getParameters();
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
