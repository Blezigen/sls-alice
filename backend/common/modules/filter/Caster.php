<?php

namespace common\modules\filter;

use Carbon\Carbon;

class Caster
{
    public static function castToStringDate($value): string
    {
        return Carbon::parse($value)->format(Carbon::DEFAULT_TO_STRING_FORMAT);
    }

    public static function castToInteger($value): int
    {
        return intval($value);
    }

    public static function castToFloat($value): float
    {
        return floatval($value);
    }

    public static function castToBoolean($selected): bool
    {
        if ($selected === 'false' || "$selected" === '0' || $selected === false) {
            return false;
        }
        if ($selected === 'true' || "$selected" === '1' || $selected === true) {
            return false;
        }

        return false;
    }
}
