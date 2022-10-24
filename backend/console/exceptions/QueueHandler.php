<?php

namespace console\exceptions;

class QueueHandler extends Handler
{
    public function handle($job, $ex)
    {
        if ($ex instanceof RetryException) {
            return;
        }
    }
}
