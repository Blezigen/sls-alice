<?php

namespace common\notifications;

use Carbon\Carbon;
use common\models\Company;
use tuyakhov\notifications\messages\DatabaseMessage;
use tuyakhov\notifications\NotificationInterface;
use tuyakhov\notifications\NotificationTrait;

class ExpireContractCompanyNotification implements NotificationInterface
{
    use NotificationTrait;

    public function __construct(
        private Company $company
    ) {}

    public function exportForDatabase(): DatabaseMessage
    {
        return new DatabaseMessage([
            'subject' => "Проблема с теплоходом",
            'body' => "Отсутствует обеспечение у компании указанной в теплоходе",
            'data' => [
                "notifiable_slug" => Carbon::parse($this->company->contract_end_at)->format("{$this->company->id}-".Carbon::DEFAULT_TO_STRING_FORMAT),
                'company_id' => $this->company->id
            ]
        ]);
    }
}