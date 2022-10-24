<?php

namespace common\modules\webhook_module;

use common\AbstractModule;
use common\modules\webhook_module\components\validators\ClassConstantDefinedValidator;
use common\modules\webhook_module\models\WebhookQuery;
use yii\base\Event;
use yii\base\Exception;

class Module extends AbstractModule
{
    public $controllerNamespace = 'common\modules\webhook_module\controllers';

    public $defaultRoute = 'webhook/index';

    public $eventDispatcherComponentClass = \common\modules\webhook_module\components\dispatcher\EventDispatcher::class;

    public $webhookClass = \common\modules\webhook_module\models\Webhook::class;

    private $webhookInterface = \common\modules\webhook_module\interfaces\WebhookInterface::class;

    private $eventDispatcherInterface = \common\modules\webhook_module\components\dispatcher\EventDispatcherInterface::class;

    public function init(): void
    {
        parent::init();

//        $this->validateWebhookClass();
//
//        $webhooks = $this->findWebhooks();
//
//        if ($webhooks) {
//            $this->validateWebhooks($webhooks);
//            $this->validateEventDispatcherComponentClass();
//
//            \Yii::configure(\Yii::$app, [
//                'components' => [
//                    'eventDispatcher' => [
//                        'class' => $this->eventDispatcherComponentClass,
//                    ],
//                    'formatter' => [
//                        'class' => \common\modules\webhook_module\components\formatter\JsonPrettyFormatter::class,
//                    ],
//                ],
//            ]);
//
//            $this->attachWebhooks($webhooks);
//        }
    }

    private function validateWebhookClass(): void
    {
        $class = new \ReflectionClass($this->webhookClass);
        if (!$class->implementsInterface($this->webhookInterface)) {
            throw new Exception($this->webhookClass . ' must implement ' . $this->webhookInterface);
        }

        $activeRecordClassNamespace = \yii\db\ActiveRecord::class;
        if (!$class->isSubclassOf($activeRecordClassNamespace)) {
            throw new Exception($this->webhookClass . ' must extend ' . $activeRecordClassNamespace);
        }
    }

    private function validateEventDispatcherComponentClass(): void
    {
        $class = new \ReflectionClass($this->eventDispatcherComponentClass);
        if (!$class->implementsInterface($this->eventDispatcherInterface)) {
            throw new Exception($this->webhookClass . ' must implement ' . $this->webhookInterface);
        }
    }

    private function validateWebhooks($webhooks): void
    {
        $validator = new ClassConstantDefinedValidator();
        foreach ($webhooks as $webhook) {
            if (!$validator->validate($webhook->event)) {
                throw new Exception('Event ' . $webhook->event . ' does not exist');
            }
        }
    }

    private function attachWebhooks(array $webhooks): void
    {
        foreach ($webhooks as $webhook) {
            Event::on($webhook->getClassName(), constant($webhook->event), function ($event) use ($webhook) {
                $this->eventDispatcher->dispatch($event, $webhook);
            });
        }
    }

//    private function detachWebhooks(array $webhooks): void
//    {
//        foreach ($webhooks as $webhook) {
//            Event::off($webhook->getClassName(), constant($webhook->event), [$this->eventDispatcher, 'dispatch']);
//        }
//    }

    private function findWebhooks(): array
    {
        return (new WebhookQuery($this->webhookClass))
            ->all();
    }

    public function routes($moduleID)
    {
        $moduleID = $this->id;

        return [
            'GET webhooks' => "$moduleID/webhook/index",
            'GET,POST webhooks/create' => "$moduleID/webhook/create",

            'GET webhook-logs' => "$moduleID/webhook-log/index",
            'GET,POST webhook-logs/create' => "$moduleID/webhook-log/create",
        ];
    }
}
