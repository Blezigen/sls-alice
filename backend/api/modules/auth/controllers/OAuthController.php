<?php

namespace api\modules\auth\controllers;

use common\AbstractController;
use common\models\User;
use yii\helpers\Url;

class OAuthController extends AbstractController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        unset($behaviors['authenticator']);

        return $behaviors;
    }

    public function runAction($id, $params = [])
    {
        $user_id = \Yii::$app->session->get('auth-user', null);

        if ($user_id) {
            $user = User::findOne($user_id);
            if ($user) {
                \Yii::$app->user->login($user);
            }
        }

        return parent::runAction($id, $params);
    }

    public function saveInSession($key)
    {
        if ($value = $this->request->get($key, null)) {
            \Yii::$app->session->set($key, $value);
        }
    }

    public function restoreInSession($key)
    {
        return \Yii::$app->session->get($key, null);
    }

    public function actionAuthorize()
    {
        $this->saveInSession('response_type');
        $this->saveInSession('client_id');
        $this->saveInSession('redirect_uri');
        $this->saveInSession('scope');
        $this->saveInSession('state');

        return $this->redirect(Url::to(['/auth']));
    }

    public function actionLogin()
    {
        if (!$this->request->get('client_id')) {
            return $this->redirect(Url::to([
                '/auth/o-auth/login',
                'response_type' => $this->restoreInSession('response_type'),
                'client_id' => $this->restoreInSession('client_id'),
                'redirect_uri' => $this->restoreInSession('redirect_uri'),
                'scope' => $this->restoreInSession('scope'),
                'state' => $this->restoreInSession('state'),
            ]), 302);
        }

        if (!\Yii::$app->user->isGuest) {
            /** @var $module \filsh\yii2\oauth2server\Module */
            $module = \Yii::$app->getModule('oauth2');

            $response = $module->getServer()->handleAuthorizeRequest(
                null,
                null,
                !\Yii::$app->user->isGuest,
                \Yii::$app->user->id
            );

            if ($response->getParameter('error')) {
//                dd($response, $response->getHttpHeader("Location"));
            }

            return $this->redirect($response->getHttpHeader('Location'));
        }

        return $this->goBack();
    }
}
