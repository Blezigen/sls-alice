<?php

namespace common\modules\order;

use common\contracts\IBootstrapSetting;
use Redbox\PersonalSettings\SettingAccess;
use Redbox\PersonalSettings\SettingSummary;
use yii\base\BootstrapInterface;

class Module extends \yii\base\Module implements BootstrapInterface, IBootstrapSetting
{
    public $controllerNamespace = 'common\modules\order\controllers';

    public function __construct($id, $parent = null, $config = [])
    {
        parent::__construct($id, $parent, $config);
        $this->init();
    }

    public function init()
    {
        parent::init();
    }

    public function bootstrap($app)
    {

    }

    public function getSettings()
    {
        return [
            SettingConstant::PLUGIN_SECTION => [
                'title' => 'Настройка модуля заказов',
                'description' => '',
                'settings' => [
                    SettingConstant::AUTO_RESERVATION_DAYS => [
                        'title' => 'Число дней для автоматического окончания резервирования заявки',
                        'description' => 'Участвует в подсчёте, через сколько дней аннулировать резервацию, это значение переопределяется у агента.',
                        'default' => 3,
                        'values' => [
                            [
                                'value' => 4,
                                'summary' => SettingSummary::make([
                                    "access" => SettingAccess::make(['roles' => ['role_user']])
                                ]),
                            ],
                            [
                                'value' => 4,
                                'summary' => SettingSummary::make([
                                    "access" => SettingAccess::make(['roles' => ['role_agent']])
                                ]),
                            ],
                        ],
                    ],

                    SettingConstant::MAX_COUNT_RESERVE_NOT_PAYMENT => [
                        'title' => 'Максимальное кол-во временных броней без оплаты',
                        'description' => 'Блокирует создание если превышено кол-во временных броней на аккаунт',
                        'default' => 2,
                        'values' => [
                            [
                                'value' => 2,
                                'summary' => SettingSummary::make([
                                    "access" => SettingAccess::make(['roles' => ['role_user']])
                                ]),
                            ],
                            [
                                'value' => 2,
                                'summary' => SettingSummary::make([
                                    "access" => SettingAccess::make(['roles' => ['role_agent']])
                                ]),
                            ],
                        ],
                    ],

                    SettingConstant::MAX_COUNT_RECEIPT_NOT_PAYMENT => [
                        'title' => 'Максимальное кол-во неоплаченных счетов',
                        'description' => 'Блокирует создание если превышено кол-во неоплаченных заказов на аккаунте',
                        'default' => 2,
                        'values' => [
                            [
                                'value' => 2,
                                'summary' => SettingSummary::make([
                                    "access" => SettingAccess::make(['roles' => ['role_user']])
                                ]),
                            ],
                            [
                                'value' => 2,
                                'summary' => SettingSummary::make([
                                    "access" => SettingAccess::make(['roles' => ['role_agent']])
                                ]),
                            ],
                        ],
                    ],

                    SettingConstant::MAX_COUNT_RESERVE_CABIN => [
                        'title' => 'Максимальное кол-во забронированных кают',
                        'description' => 'Задаёт максимальное кол-во кают которое можно забронировать.',
                        'default' => 6,
                        'values' => [
                            [
                                'value' => 6,
                                'summary' => SettingSummary::make([
                                    "access" => SettingAccess::make(['roles' => ['role_user']])
                                ]),
                            ],
                            [
                                'value' => 6,
                                'summary' => SettingSummary::make([
                                    "access" => SettingAccess::make(['roles' => ['role_agent']])
                                ]),
                            ],
                        ],
                    ],

                    SettingConstant::MAX_DISCOUNT => [
                        'title' => 'Доступное максимальное количество скидки',
                        'description' => '',
                        'default' => 30,
                    ],

                    SettingConstant::COUNT_EXPIRED_DAYS_FOR_FILTER => [
                        'title' => 'Количество дней до истечения заявки (для фильтрации)',
                        'description' => '',
                        'default' => 3
                    ]
                ],
            ],
        ];
    }
}
