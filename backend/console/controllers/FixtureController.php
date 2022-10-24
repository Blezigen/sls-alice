<?php

namespace console\controllers;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;
use tests\modules\ActiveFixture;
use yii\test\Fixture;

class FixtureController extends \yii\faker\FixtureController
{
    /**
     * @var mixed|ConsoleOutput
     */
    protected $output;

    public function runAction($id, $params = [])
    {
        $this->output = new ConsoleOutput(ConsoleOutput::VERBOSITY_NORMAL, true);
        return parent::runAction($id, $params);
    }

    /**
     * Loads the specified fixtures.
     * This method will call [[Fixture::load()]] for every fixture object.
     * @param Fixture[]|null $fixtures the fixtures to be loaded. If this parameter is not specified,
     * the return value of [[getFixtures()]] will be used.
     */
    public function loadFixtures($fixtures = null)
    {
        $section1 = $this->output->section();
        $section2 = $this->output->section();
        ProgressBar::setFormatDefinition('custom', "File: %message%\n[%bar%] %current:6s%/%max:6s% %percent:3s%% %elapsed:6s% %memory:6s%");
        ProgressBar::setFormatDefinition('file', "  [%bar%] %current:6s%/%max:6s% %percent:3s%% %elapsed:6s% %memory:6s% -- %message%");
        $progressBar = new ProgressBar($section1);
        $progressBar->setFormat('custom');
        $progressBar->setMessage('Task is in progress...');

        if ($fixtures === null) {
            $fixtures = $this->getFixtures();
        }

        $progressBar->setMaxSteps(count($fixtures));
        $progressBar->setMessage(static::class);

        /* @var $fixture Fixture */
        foreach ($fixtures as $fixture) {
            $fixture->beforeLoad();
        }

        foreach ($fixtures as $fixture) {
            $progressBar->setMessage($fixture::class);
            if ($fixture instanceof ActiveFixture){
                $fixture->output = $section2;
            }
            $fixture->load();
            $progressBar->advance();
        }

        foreach (array_reverse($fixtures) as $fixture) {
            $fixture->afterLoad();
        }
        $progressBar->finish();
    }
}