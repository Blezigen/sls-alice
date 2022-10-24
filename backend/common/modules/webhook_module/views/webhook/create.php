<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model \degordian\webhooks\models\Webhook */

$this->title = 'Create Webhook';
$this->params['breadcrumbs'][] = ['label' => 'Webhooks', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="webhook-create">

    <h1><?php echo Html::encode($this->title); ?></h1>

    <?php echo $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
