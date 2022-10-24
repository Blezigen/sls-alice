<?php

namespace common\modules\acceptance\contracts;

use common\modules\acceptance\models\Acceptance;

interface IAcceptanceProvider
{
    public function createAcceptance($phone, $code, $generateAttempt = 1);

    public function getAcceptance($phone): ?Acceptance;

    public function useAcceptance($phone, $accept_token);

    public function sendCode($phone);

    public function getAcceptOnCode($phone, $code);
}
