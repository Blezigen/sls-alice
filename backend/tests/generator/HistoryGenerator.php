<?php

namespace tests\generator;

use Carbon\Carbon;

class HistoryGenerator extends Generator
{
    public function __construct(array $config)
    {
        $config['created_at'] = Carbon::now();
//        $config['updated_at'] = null;
//        $config['deleted_at'] = null;
//        $config['created_acc'] = null;
//        $config['updated_acc'] = null;
//        $config['deleted_acc'] = null;
        parent::__construct($config);
    }
}