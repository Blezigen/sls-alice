<?php

namespace api\modules\auth;

class SettingConstant
{
    public const PLUGIN_SECTION = 'PS_AUTH_08032022';
    public const REQUIRED_TWO_FACTOR = self::PLUGIN_SECTION . '_REQ_TWO_FACTOR';
    public const REQUIRED_IP_CHECK = self::PLUGIN_SECTION . '_REQ_IP_CHECK';
}
