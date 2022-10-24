<?php

/* @var $this yii\web\View */

/* @var $dataProvider \yii\data\ActiveDataProvider */

use yii\grid\GridView;

$this->title = 'Вход в систему';
?>
<div class="align-content-center">
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
    <div class="mt-5 offset-lg-3 col-lg-6">
        <?php echo GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                'id',
                'username',
                [
                    'label' => 'Role',
                    'value' => function ($model) {
                        return implode(
                            ', ',
                            array_map(function ($role) {
                                return $role->name;
                            },
                                Yii::$app->authManager->getRolesByUser($model->id))
                        );
                    },
                ],
                'fullName',
                [
                    'label' => 'Действия',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return "<form method='post'><input type='hidden' name='user_id' value='{$model->id}'><button type='submit' class='btn btn-dark'>Войти</button></form>";
                    },
                ],
            ],
        ]);
        ?>
    </div>
</div>