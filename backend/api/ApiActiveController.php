<?php

namespace api;

use common\AbstractActiveController;

abstract class ApiActiveController extends AbstractActiveController
{
    public $serializer = [
        'class' => RestSerializer::class,
        'collectionEnvelope' => 'data',
    ];

    public function __docs($path, $action)
    {
        return [];
    }
}
