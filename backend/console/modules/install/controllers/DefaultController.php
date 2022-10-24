<?php

namespace console\modules\install\controllers;

use console\AbstractConsoleController;
use console\modules\install\actions\InstallCollectionAction;
use console\modules\install\actions\InstallSettingAction;

class DefaultController extends AbstractConsoleController
{
    public function actions()
    {
        return [
            'setting' => [
                'class' => InstallSettingAction::class,
                'resources' => [
                    '@install_resources/setting/default.php',
                ],
            ],
            'collection' => [
                'class' => InstallCollectionAction::class,
                'resources' => [
                    '@install_resources/collections/default.php',
                ],
            ],
        ];
    }
}
