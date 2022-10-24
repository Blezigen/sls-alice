<?php

namespace common\modules\sender\providers\unisender;

use common\components\services\UnisenderService;
use common\modules\sender\contracts\ISenderComponent;
use common\modules\sender\contracts\ISenderProvider;
use common\modules\sender\providers\unisender\contracts\IUnisenderInitialor;
use common\modules\sender\providers\unisender\contracts\IUnisenderReceiver;
use common\modules\sender\SettingConstant;
use GuzzleHttp\Client;

class SenderProvider implements ISenderProvider
{
    /** @var ISenderComponent */
    public $service;

    /**
     * @param  IUnisenderReceiver  $receiver
     * @param  IUnisenderInitialor  $object
     */
    public function canBeProcessed($receiver, $object): bool
    {
        if (!$receiver instanceof IUnisenderReceiver || !$object instanceof IUnisenderInitialor) {
            return false;
        }

        return true;
    }

    public function getUnisenderToken()
    {
        return \Yii::$app->user->get(
            SettingConstant::PLUGIN_SECTION,
            SettingConstant::UNISENDER_TOKEN,
            null
        );
    }

    public function getUnisenderEmail()
    {
        return \Yii::$app->user->get(
            SettingConstant::PLUGIN_SECTION,
            SettingConstant::UNISENDER_EMAIL,
            null
        );
    }

    public function getUnisenderSenderName()
    {
        return \Yii::$app->user->get(
            SettingConstant::PLUGIN_SECTION,
            SettingConstant::UNISENDER_SENDER_NAME,
            null
        );
    }

    /**
     * @param  IUnisenderReceiver  $receiver
     * @param  IUnisenderInitialor  $object
     *
     * @throws \Exception
     */
    public function execute($receiver, $object): bool
    {
        $emails = $receiver->getEmails();
        $listTitle = $object->getListTitle();
        $templateTitle = $object->getTemplateTitle();
        $params = $object->getTemplateParams();

        if (!$this->service->isEmulate()) {
            $service = (new UnisenderService([
                'key' => $this->getUnisenderToken(),
                'senderEmail' => $this->getUnisenderEmail(),
                'senderName' => $this->getUnisenderSenderName(),
                'client' => new Client(),
            ]));

            $list = $service->getListByTitle($listTitle, true);
            $listId = $list['id'];

            $template = $service->getTemplateByTitle($templateTitle);
            $templateId = $template['id'];
            $templateSubject = $template['subject'];

            $html = $template['body'];
            $loader = new \Twig\Loader\ArrayLoader([
                'index' => $html,
            ]);
            $twig = new \Twig\Environment($loader);
            $body = $twig->render('index', $params);

            $service->importContacts($emails, [$listId]);
            $messageId = $service->addMessage($listId, $templateId, $templateSubject, $body, []);
            $service->sendMessage($emails, $messageId);
        }

        if ($this->service->isDebug()) {
            $message = [
                'Отправлено электронное сообщение',
                "Список получателя: $listTitle",
                "Шаблон сообщения: $templateTitle",
                'Получателей: ' . count($emails),
                '',
                'Параметры:',
            ];
            foreach ($params as $key => $value) {
                $message[] = "$key) " . ($value ?? 'null');
            }
            $this->service->debug(implode("\n", $message), 'unisender');
        }

        return true;
    }
}
