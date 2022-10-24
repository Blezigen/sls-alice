<?php

namespace integration\jobs;

use yii\base\BaseObject;
use yii\queue\JobInterface;
use integration\services\MintransService;

class MintransJob extends BaseObject implements JobInterface
{
  public string $filename;

  public function execute($queue)
  {
    $service = new MintransService();
    $result = $service->getResult($this->filename);

    if (!$result)
      return false;

    /*
      Обработка результата
    */

    return true;
  }
}
