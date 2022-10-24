<?php

namespace api;

use common\AbstractController;

abstract class ApiController extends AbstractController
{
    public $serializer = Serializer::class;
}
