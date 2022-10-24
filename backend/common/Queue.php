<?php

namespace common;

use console\QueueOutput;
use Symfony\Component\Console\Output\OutputInterface;
use yii\console\Application;
use yii\queue\ExecEvent;
use yii\queue\JobInterface;

class Queue extends \yii\queue\db\Queue
{
    /** @var QueueOutput */
    protected $output = null;

    public function handle($id, JobInterface $job, $ttr)
    {
        return $this->handleMessage($id, $this->serializer->serialize($job), $ttr, 1);
    }

    public function consoleLog($message, $writeLn = false)
    {
        if (\Yii::$app instanceof Application) {
            if ($this->output === null) {
                $this->output = new QueueOutput();
            }

            $this->output->write($message, $writeLn, OutputInterface::OUTPUT_NORMAL);
            echo $this->output->fetch();
        }
    }

    /**
     * @param string $id of a job message
     * @param string $message
     * @param int $ttr time to reserve
     * @param int $attempt number
     *
     * @return bool
     */
    protected function handleMessage($id, $message, $ttr, $attempt)
    {
        list($job, $error) = $this->unserializeMessage($message);
        $event = new ExecEvent([
            'id' => $id,
            'job' => $job,
            'ttr' => $ttr,
            'attempt' => $attempt,
            'error' => $error,
        ]);
        $this->trigger(self::EVENT_BEFORE_EXEC, $event);
        if ($event->handled) {
            return true;
        }
        if ($event->error) {
            return $this->handleError($event);
        }
        try {
            $this->consoleLog('exec(' . get_class($event->job) . ')', true);
            $event->result = $event->job->execute($this);
        } catch (\Exception $error) {
            $event->error = $error;

            return $this->handleError($event);
        } catch (\Throwable $error) {
            $event->error = $error;

            return $this->handleError($event);
        }
        $this->trigger(self::EVENT_AFTER_EXEC, $event);

        return true;
    }

    /**
     * @return bool
     */
    public function handleError(ExecEvent $event)
    {
        try {
            if (method_exists($event->job, 'handleException')) {
                $event->job->handleException($event->error);

                return true;
            }
        } catch (\Throwable $e) {
            $event->error = $e;

            return parent::handleError($event);
        }

        return false;
    }
}
