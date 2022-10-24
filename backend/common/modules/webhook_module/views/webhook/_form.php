<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model \degordian\webhooks\models\Webhook */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="webhook-form">

    <?php $form = ActiveForm::begin(); ?>

    <?php echo $form->field($model, 'event')->textInput(['maxlength' => true]); ?>

    <?php echo $form->field($model, 'description')->textInput(); ?>

    <?php echo $form->field($model, 'url')->textInput(['maxlength' => true]); ?>

    <?php echo $form->field($model, 'method')->textInput(['maxlength' => true]); ?>

    <div class="form-group">
        <?php echo Html::submitButton('Save', ['class' => 'btn btn-success']); ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
