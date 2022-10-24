<?php

namespace console;

use common\jobs\CheckReservationJob;
use yii\base\Application;
use yii\base\BootstrapInterface;

class Bootstrap implements BootstrapInterface
{
    public function bootstrap($app)
    {
        if ($app instanceof \yii\console\Application) {
            if ($app->has('schedule')) {
                /** @var \omnilight\scheduling\Schedule $schedule */
                $schedule = $app->get('schedule');
                // Place all your shedule command below
                $schedule->call(function($app) {
                    echo "PUSH: CheckReservationJob".PHP_EOL;
                    \Yii::$app->queue->push(new CheckReservationJob());
                })->everyMinute();
            } else {
                echo "Component 'schedule' NOT FOUND.";
            }
        }
    }

}