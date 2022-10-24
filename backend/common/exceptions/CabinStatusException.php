<?php

namespace common\exceptions;

use OpenApi\Annotations\OpenApi;
use OpenApi\Annotations\Property;
use OpenApi\Annotations\Schema;
use Redbox\JsonRpc\Exceptions\Exception;
use yii\web\HttpException;

class CabinStatusException extends Exception
{
    public $statusCode = 400;
    public array $problems;
    public array $hints;

    public function __construct(array $problems = [], array $hints = [])
    {
        parent::__construct('Присутствуют ошибки, требующие исправления!', $this->statusCode, [
            "problems" => $problems,
            "hints" => $hints,
        ]);
        $this->problems = $problems;
        $this->hints = $hints;
    }
}
