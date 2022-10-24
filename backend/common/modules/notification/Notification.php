<?php

namespace common\modules\notification;

/**
 * Database notification model
 *
 * @property string $notifiable_slug
 */
class Notification extends \tuyakhov\notifications\models\Notification
{
    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            ['notifiable_slug', 'string'],
            ...parent::rules()
        ];
    }
}