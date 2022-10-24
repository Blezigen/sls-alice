<?php

namespace common\modules\sender;

class SettingConstant
{
    public const PLUGIN_SECTION = 'PS_SENDER_08032022';

    public const DEBUG = self::PLUGIN_SECTION . '_DEBUG';
    public const EMULATE = self::PLUGIN_SECTION . '_EMULATE';
    public const EMULATE_TELEGRAM_CHAT_ID = self::PLUGIN_SECTION . '_EMULATE_TELEGRAM_CHAT_ID';
    public const EMULATE_TELEGRAM_TOKEN = self::PLUGIN_SECTION . '_EMULATE_TELEGRAM_TOKEN';

    public const UNISENDER_TOKEN = self::PLUGIN_SECTION . '_UNISENDER_TOKEN';
    public const UNISENDER_EMAIL = self::PLUGIN_SECTION . '_UNISENDER_EMAIL';
    public const UNISENDER_SENDER_NAME = self::PLUGIN_SECTION . '_UNISENDER_SNAME';

    public const TELEGRAM_TOKEN = self::PLUGIN_SECTION . '_TELEGRAM_TOKEN';
}
