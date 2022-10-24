<?php

namespace console\controllers;

use common\components\services\TemplateService;

class TemplateController extends \yii\console\Controller
{
    public function actionInit(){
        $template = new TemplateService();
//        $param['name'] = 'Вася Email';
//        echo $template->getTemplateEmail('mail_success', 'Вася');
        $param['name'] = 'Вася SMS';
        $param['code'] = '565765756';
        //echo $template->getTemplateSMS('sms_kod', $param);

        echo $template->UnisenderLoad();
    }
}