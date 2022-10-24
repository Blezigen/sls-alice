<?php

namespace common\components\services;

use common\models\SenderTemplate;
use GuzzleHttp\Client;
use yii\base\Component;
use yii\db\JsonExpression;

class TemplateService extends Component
{
    public $api_key = null;

    public function getTemplate($course, $slug)
    {
        $sender_template = SenderTemplate::find()->where(['source' => $course, 'slug' => $slug])->one();
        if (!$sender_template) {
            throw new \Exception('Шаблон не найден');
        }

        return $sender_template;
    }

    public function UnisenderLoad()
    {
        if ($this->api_key) {
            $client = new Client([
                'base_uri' => 'https://api.unisender.com',
            ]);

            $result = $client->request('GET', "/ru/api/getTemplates?format=json&api_key={$this->api_key}");
            $result = json_decode($result->getBody()->getContents(), true);

            foreach ($result as $item) {
                foreach ($item as $value) {
                    $unsend = SenderTemplate::find()->where(['template_id' => $value['id']])->one();
                    if (!$unsend) {
                        $unsend = new SenderTemplate();
                        $unsend->source = 'unisender';
                        $unsend->slug = $value['title'];
                        $unsend->template_id = $value['id'];
                        $unsend->subject = $value['subject'];
                        $unsend->html = $value['body'];
                        $unsend->summary_json = new JsonExpression([
                            'id' => $value['id'],
                        ]);

                        $unsend->save();
                    }
                    $unsend->slug = $value['title'];
                    $unsend->subject = $value['subject'];
                    $unsend->html = $value['body'];
                    $unsend->summary_json = new JsonExpression([
                        'id' => $value['id'],
                    ]);
                    $unsend->save();
                }
            }

            return true;
        }

        throw new \Exception('Не задан api_key unisender');
    }
}
