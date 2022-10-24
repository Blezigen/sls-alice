<?php
/**
 * @see http://www.yiiframework.com/
 *
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace common\modules\acceptance\behaviors;

use common\modules\acceptance\AcceptanceServices;
use common\modules\acceptance\contracts\IAcceptanceManager;
use yii\base\Model;
use yii\behaviors\AttributeBehavior;

class AcceptTokenBehavior extends AttributeBehavior
{
    public $acceptTokenAttribute = 'accept_token';
    public $phoneAttribute = 'phone';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        if (empty($this->attributes)) {
            $this->attributes = [
                Model::EVENT_AFTER_VALIDATE => [$this->acceptTokenAttribute],
            ];
        }
    }

    /**
     * {@inheritdoc}
     *
     * In case, when the [[value]] is `null`, the result of the PHP function [time()](https://www.php.net/manual/en/function.time.php)
     * will be used as value.
     */
    protected function getValue($event)
    {
        if (!$this->value) {
            $phone = $event->sender->{$this->phoneAttribute};
            $acceptToken = $event->sender->{$this->acceptTokenAttribute};

            /** @var IAcceptanceManager|AcceptanceServices $service */
            $service = \Yii::$app->get(IAcceptanceManager::class);
            $service->useAcceptToken($phone, $acceptToken);
        }

        return $this->value;
    }
}
