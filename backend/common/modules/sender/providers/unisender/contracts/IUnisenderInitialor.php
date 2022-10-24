<?php

namespace common\modules\sender\providers\unisender\contracts;

use common\modules\sender\contracts\ISenderInitiator;

interface IUnisenderInitialor extends ISenderInitiator
{
    public function getListTitle(): string;

    public function getTemplateTitle(): string;

    public function getTemplateParams(): array;
}
