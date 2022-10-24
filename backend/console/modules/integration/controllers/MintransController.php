<?php

namespace console\modules\integration\controllers;

use console\AbstractConsoleController;
use integration\services\MintransService;

/*
    Пример интерации Минтранс
*/

class MintransController extends AbstractConsoleController
{
    public function actionIndex()
    {
        $filename = $this->service()->sendTours();

        if ($filename) {
            $this->consoleLog("<green>Отправлен файл: {$filename}</green>", true);
        } else {
            $this->consoleLog("<err>Ошибка отправки файла</err>", true);
        }
    }

    public function actionStation()
    {
        $filename = $this->service()->sendStation();

        if ($filename) {
            $this->consoleLog("<green>Отправлен файл с информацией по городам: {$filename}</green>", true);
        } else {
            $this->consoleLog("<err>Ошибка отправки файла</err>", true);
        }
    }

    public function actionTimetable()
    {
        $filename = $this->service()->sendTimetable();

        if ($filename) {
            $this->consoleLog("<green>Отправлен файл с расписанием: {$filename}</green>", true);
        } else {
            $this->consoleLog("<err>Ошибка отправки файла</err>", true);
        }
    }

    public function actionResult($filename, $fileType = null)
    {
        $result = $this->service()->getResult($filename, $fileType);

        if (isset($result["entry"]["fault"])) {
            $err = $result["entry"]["fault"]["@attributes"];
            $this->consoleLog("<err>{$err['description']}</err>", true);
        } else {
            $this->consoleLog("ErrCode: {$result['@attributes']['errCode']}", true);
        }
    }

    protected function service()
    {
        return new MintransService();
    }
}
