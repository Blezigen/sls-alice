<?php

namespace api\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@cdn';

    public $css = [
        'static/library/bootstrap-5.1.3-dist/css/bootstrap.min.css',
    ];

    public $js = [
        'static/library/bootstrap-5.1.3-dist/js/bootstrap.min.js',
    ];

    public $depends = [
    ];
}
