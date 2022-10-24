<?php

namespace doc\exceptions;

use yii\web\Response;

class Handler extends \api\exceptions\Handler
{
    protected function renderException($ex)
    {
        if ($this->exception instanceof SwaggerException) {
            if (\Yii::$app->has('response')) {
                $response = \Yii::$app->getResponse();
                // reset parameters of response to avoid interference with partially created response data
                // in case the error occurred while sending the response.
                $response->isSent = false;
                $response->stream = null;
                $response->data = null;
                $response->content = null;
            } else {
                $response = new Response();
            }

            $openApi = \Yii::$app->swagger->getOpenApi();
            $openApi->info->description = "### <font color='red'>Ошибка: {$this->exception->getMessage()}</font>";
            $openApi->info->description .= "\nДля исправления ошибки воспользуйтесь консольной командой. \n\n```bash\nphp yii swagger/change/add \"Описание\"\n```";
            $response->content = $openApi->toYaml();
            $response->send();
            \Yii::$app->end();
        }

        parent::renderException($ex);
    }
}
