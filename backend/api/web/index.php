<?php

include __DIR__ . '/../../helpers.php';
require __DIR__ . '/../../vendor/autoload.php';

defined('YII_DEBUG') or define('YII_DEBUG', env("APP_DEBUG") === "true");
defined('YII_ENV') or define('YII_ENV', env("APP_ENV") ?? "dev");

require __DIR__ . '/../../Yii.php';
\Yii::setAlias('@vendor', __DIR__ . '/../../vendor');

require __DIR__ . '/../../common/config/bootstrap.php';
require __DIR__ . '/../config/bootstrap.php';

$config = require __DIR__ . '/../config/main.php';

(new yii\web\Application($config))->run();
