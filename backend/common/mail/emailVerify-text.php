<?php

/* @var $this yii\web\View */
/* @var $user common\models\User */

$verifyLink = Yii::$app->urlManager->createAbsoluteUrl(['site/verify-email', 'token' => $user->verification_token]);
?>
Hello <?php echo $user->username; ?>,

Follow the link below to verify your email:

<?php echo $verifyLink; ?>
