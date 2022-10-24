<?php

use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel \degordian\webhooks\models\WebhookSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Webhooks';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="webhook-index">

    <h1><?php echo Html::encode($this->title); ?></h1>

    <p>
        <?php echo Html::a('Create Webhook', ['create'], ['class' => 'btn btn-success']); ?>
    </p>

    <?php echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'id',
            'event',
            'url:url',
            'method',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
