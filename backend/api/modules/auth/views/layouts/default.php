<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;

\api\assets\AppAsset::register($this);
?>
<?php $this->beginPage(); ?>
    <!DOCTYPE html>
    <html lang="<?php echo Yii::$app->language; ?>" class="h-100">
    <head>
        <meta charset="<?php echo Yii::$app->charset; ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <?php $this->registerCsrfMetaTags(); ?>
        <title><?php echo Html::encode($this->title); ?></title>
        <?php $this->head(); ?>
    </head>
    <body class="d-flex flex-column h-100">
    <?php $this->beginBody(); ?>

    <main role="main">
        <div class="container">
            <?php echo $content; ?>
        </div>
    </main>

    <?php $this->endBody(); ?>
    </body>
    </html>
<?php $this->endPage();
