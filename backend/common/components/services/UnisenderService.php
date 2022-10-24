<?php

namespace common\components\services;

use Carbon\Carbon;
// use Illuminate\Support\Facades\Log;
use Psr\Http\Client\ClientInterface;
use yii\base\Component;
use yii\base\ErrorException;

class UnisenderService extends Component
{
    public ?string $key;
    public string $senderEmail = '';
    public string $senderName = '';
    public string $endPoint = 'https://api.unisender.com/ru/api';
    public ?ClientInterface $client = null;

    protected function sendHttp($method, $url, $payload = []): array
    {
        $request = [
            'header' => ['Content-Type', 'application/json'],
            'form_params' => array_merge([
                'api_key' => $this->key,
                'format' => 'json',
            ], $payload),
        ];
        $response = $this->client->$method($this->endPoint . $url, $request);
        $data = json_decode((string) $response->getBody(), true);

        if (!array_key_exists('result', $data)) {
            throw new ErrorException('Param "result" not found');
        }

        return $data;
    }

    public function getLists()
    {
        $response = $this->sendHttp('post', '/getLists?format=json', []);

        return $response['result'];
    }

    public function getTemplates()
    {
        $response = $this->sendHttp('post', '/getTemplates?format=json', []);

        return $response['result'];
    }

    public function getListByTitle($listTitle, $createNotExist = false)
    {
        $lists = $this->getLists();
        foreach ($lists as $list) {
            if (mb_strpos($list['title'] ?? '', $listTitle) !== false) {
                return $list;
            }
        }
        if (!$createNotExist) {
            throw new \Exception('[Unisender] Список не найден');
        }

        $response = $this->createList($listTitle);

        return $response['result'];
    }

    public function getTemplateByTitle($templateTitle)
    {
        $templates = $this->getTemplates();
        foreach ($templates as $template) {
            if (mb_strpos($template['title'] ?? '', $templateTitle) !== false) {
                return $template;
            }
        }
        throw new \Exception('[Unisender] Шаблон не найден');
    }

    public function addMessage($listId, $templateId, $subject, $text, $attachments = [])
    {
        $payload = [
            'template_id' => $templateId,
            'list_id' => $listId,
        ];

        $payload['sender_email'] = $this->senderEmail;
        $payload['sender_name'] = $this->senderName;
        $payload['subject'] = $subject;
        $payload['generate_text'] = 1;
        $payload['body'] = $text;

        foreach ($attachments as $fileName => $attachment) {
            $payload['attachments'][$fileName] = file_get_contents($attachment);
        }

        $data = $this->sendHttp('post', '/createEmailMessage?format=json', $payload);

        if (array_key_exists('message_id', $data['result']) == false) {
            throw new ErrorException(json_encode($data));
        }

        return $data['result']['message_id'] ?? null;
    }

    public function sendMessage($emails, $messageIds, $timeout = 1)
    {
        $payload = [
            'message_id' => $messageIds,
            'track_read' => 1,
            'track_links' => 1,
            'start_time' => Carbon::now()->addMinutes($timeout)->format('Y-m-d H:i'),
        ];

        $payload['contacts'] = implode(', ', $emails);
        $data = $this->sendHttp('post', '/createCampaign?format=json', $payload);

        return $data;
    }

    public function createList($title)
    {
        $payload = [
            'title' => $title,
        ];
        $data = $this->sendHttp('post', '/createList?format=json', $payload);

        Log::channel('email_stack')->info('Создание списка:', [
            'payload' => $payload,
            'response' => $data,
        ]);

        return $data;
    }

    public function importContacts($emails, $listIds = [])
    {
        $field_names = ['email', 'email_list_ids'];
        $data = [];
        foreach ($emails as $key => $email) {
            $data[$key] = ["$email",  implode(',', $listIds)];
        }

        $payload = [
            'field_names' => $field_names,
            'data' => $data,
        ];

        $data = $this->sendHttp('post', '/importContacts?format=json', $payload);

        if (array_key_exists('total', $data['result']) == false) {
            throw new ErrorException(json_encode($data));
        }

        return $data;
    }

    public function createCampaign($messageIds, $timeout = 15)
    {
        $payload = [
            'message_id' => $messageIds,
            'track_read' => 1,
            'track_links' => 1,
            'start_time' => Carbon::now()->addMinutes($timeout)->format('Y-m-d H:i'),
        ];

        $data = $this->sendHttp('post', '/createCampaign?format=json', $payload);

        return $data;
    }
}
