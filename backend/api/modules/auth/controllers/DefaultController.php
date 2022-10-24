<?php

namespace api\modules\auth\controllers;

use api\modules\auth\forms\LoginForm;
use api\modules\auth\models\User;
use common\AbstractController;
use yii\data\ActiveDataProvider;
use yii\web\Response;

class DefaultController extends AbstractController
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
            $account = User::findOne($user_id);
            if ($account) {
                \Yii::$app->user->login($account);
            }
        }

        return parent::runAction($id, $params);
    }

    public function actionLoginView()
    {
        $this->layout = 'default';

        $model = new LoginForm();
        if ($model->load(\Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';

        $this->response->format = Response::FORMAT_HTML;

        $dataProvider = new ActiveDataProvider([
            'query' => \common\models\User::find(),
            'pagination' => false,
        ]);

        return $this->render('login-view', [
            'model' => $model,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionLogin()
    {
        $user_id = $this->request->post('user_id');
        if ($user_id && $user = User::findOne($user_id)) {
            \Yii::$app->user->login($user, 3600 * 24 * 30);
            \Yii::$app->session->set('auth-user', $user_id);

            return $this->redirect(['/auth/default/login-view']);
        }

        $model = new LoginForm();
        if ($model->load(\Yii::$app->request->post()) && $model->login()) {
            \Yii::$app->session->set('auth-user', $user_id);

            return $this->redirect(['/auth/default/login-view']);
        }

        $model->password = '';

        return $this->goBack();
    }

    public function actionLogout()
    {
        \Yii::$app->session->remove('auth-user');

        if (!\Yii::$app->user->isGuest) {
            \Yii::$app->user->logout(false);
        }

        return $this->redirect(['/auth/default/login-view']);
    }
}
