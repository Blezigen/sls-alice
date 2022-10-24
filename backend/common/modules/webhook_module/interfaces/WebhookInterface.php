<?php

namespace common\modules\webhook_module\interfaces;

interface WebhookInterface
{
    public function getEvent();

    public function getUrl();

    public function getMethod();

    public function getEventName();

    public function getClassName();
}
