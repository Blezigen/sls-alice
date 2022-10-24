<?php

/* @var $this yii\web\View */

/* @var $dataProvider \yii\data\ActiveDataProvider */

use yii\grid\GridView;

$username = 'guest';
$role = 'guest';
if (!Yii::$app->user->isGuest) {
    $username = Yii::$app->user->identity->username;
    $role = Yii::$app->user->identity->role;
}

$this->title = 'Вход в систему';
?>
<div class="align-content-center">
    <div class="mt-5 offset-lg-3 col-lg-6 align-content-center">
        <?php echo $this->render('../logo'); ?>
    </div>
    <?php if (!Yii::$app->user->isGuest) { ?>
    <div class="mt-4 offset-lg-3 col-lg-6">
        <div class="card">
            <div class="card-header  text-center">
                <h1><?php echo \yii\helpers\Html::encode('Добро пожаловать!'); ?></h1>
            </div>
            <div class="card-body">
                <div class="card">
                    <div class="card-body">
                        <div class="row justify-content-center align-items-center" >
                            <div class="col text-left">
                                Имя пользователя: <?php echo $username; ?><br>
                                Роль: <?php echo $role; ?><br>
                            </div>
                            <div class="col  text-center">
                                <form method='post' action='auth/logout'>
                                    <input type='hidden' name='user_id' value='{$model->id}'>
                                    <button type='submit' class='btn btn-dark w-100'>Выйти из аккаунта</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="row justify-content-center">
                    <div class="col text-center">
                        <form method='post' action='oauth/login'>
                            <input type='hidden' name='user_id' value='{$model->id}'>
                            <button type='submit' class='btn btn-success w-100'>Разрешить доступ</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php } else { ?>
    <div class="mt-5 offset-lg-3 col-lg-6">
        <h1><?php echo \yii\helpers\Html::encode($this->title); ?></h1>

        <p>Обычный вход:</p>

        <?php $form = \yii\widgets\ActiveForm::begin(['id' => 'login-form']); ?>

        <?php echo $form->field($model, 'username')
            ->textInput(['autofocus' => true]); ?>

        <?php echo $form->field($model, 'password')->passwordInput(); ?>

        <?php echo $form->field($model, 'rememberMe')->checkbox(); ?>

        <div class="form-group">
            <?php echo \yii\helpers\Html::submitButton('Login', [
                'class' => 'btn btn-primary btn-block',
                'name' => 'login-button',
            ]); ?>
        </div>

        <?php \yii\widgets\ActiveForm::end(); ?>

    </div>
    <?php } ?>
    <div class="mt-5 offset-lg-3 col-lg-6">
        <?php echo GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                'id',
                'username',
                [
                    'label' => 'Role',
                    'value' => function ($model) {
                        return $model->role;
                    },
                ],
//                'fullName',
                [
                    'label' => 'Действия',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return "<form method='post' action='auth/login'><input type='hidden' name='user_id' value='{$model->id}'><button type='submit' class='btn btn-dark w-100'>Войти</button></form>";
                    },
                ],
            ],
        ]);
?>
    </div>
</div>