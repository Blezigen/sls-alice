<?php

namespace common\events;

use common\BaseActiveRecord;
use yii\base\Event;

class CanReadAttributesEvent extends Event
{
    public const EVENT_CAN_READ_ATTRIBUTE = "EVENT_CAN_READ_ATTRIBUTES";

    public BaseActiveRecord $model;

    public array $fields;
    public array|null $fieldAccessed = null;

    public function getOnlyAccessedFields(): array
    {
        return $this->fields;
        return $this->fieldAccessed ?? $this->fields;
    }
}