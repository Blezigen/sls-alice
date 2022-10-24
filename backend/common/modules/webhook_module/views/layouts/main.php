<?php

/* @var $this \yii\web\View */

/* @var $content string */

use yii\bootstrap\Alert;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

?>
<?php $this->beginPage(); ?>
<!DOCTYPE html>
<html lang="<?php echo Yii::$app->language; ?>">
<head>
    <meta charset="<?php echo Yii::$app->charset; ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php echo Html::csrfMetaTags(); ?>
    <title><?php echo Html::encode($this->title); ?></title>
    <?php $this->head(); ?>
</head>
<body>
<?php $this->beginBody(); ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => 'webhooks',
        'brandUrl' => \yii\helpers\Url::toRoute(['webhook/index']),
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    $menuItems = [
        ['label' => 'Список webhooks', 'url' => Url::toRoute(['/webhooks'])],
        ['label' => 'Логи webhooks', 'url' => Url::toRoute(['webhook-log/index'])],
    ];

    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => $menuItems,
    ]);
    NavBar::end();
    ?>

    <div class="container">
        <?php echo Breadcrumbs::widget([
            'homeLink' => [
                'label' => 'yii2-webhooks',
                'url' => Url::toRoute(['/webhooks']),
            ],
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]); ?>
        <?php echo Alert::widget(); ?>
        <?php echo $content; ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; <?php echo Html::encode(Yii::$app->name); ?> <?php echo date('Y'); ?></p>

        <p class="pull-right"><?php echo Yii::powered(); ?></p>
    </div>
</footer>

<?php $this->endBody(); ?>
</body>
</html>
<?php $this->endPage(); ?>
