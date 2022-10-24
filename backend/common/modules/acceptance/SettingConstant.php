<?php

namespace common\modules\acceptance;

class SettingConstant
{
    public const PLUGIN_SECTION = 'PS_ACCEPTANCE_08032022';

    public const GENERATE_ATTEMPT_MAX
        = SettingConstant::PLUGIN_SECTION . '_GENERATE_ATTEMPT_MAX';

    public const DELAY
        = SettingConstant::PLUGIN_SECTION . '_DELAY';

    public const CODE_ATTEMPT_MAX
        = SettingConstant::PLUGIN_SECTION . '_CODE_ATTEMPT_MAX';
}
