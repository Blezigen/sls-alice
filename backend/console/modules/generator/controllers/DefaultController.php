<?php

namespace console\modules\generator\controllers;

use console\AbstractConsoleController;
use Yii;
use yii\base\InlineAction;
use yii\gii\console\GenerateAction;

class DefaultController extends AbstractConsoleController
{
    /**
     * @var \yii\gii\Module
     */
    public $module;
    /**
     * @var bool whether to overwrite all existing code files when in non-interactive mode.
     * Defaults to false, meaning none of the existing code files will be overwritten.
     * This option is used only when `--interactive=0`.
     */
    public $overwrite = false;
    /**
     * @var array a list of the available code generators
     */
    public $generators = [
        'model' => ['class' => \console\modules\generator\generators\database_model\Generator::class],
        'module' => ['class' => \console\modules\generator\generators\module\Generator::class],
        'meta' => ['class' => \console\modules\generator\generators\migrations\Generator::class],
        'procedure' => ['class' => \console\modules\generator\generators\procedure\Generator::class],
    ];

    /**
     * @var array generator option values
     */
    private $_options = [];

    /**
     * {@inheritdoc}
     */
    public function __get($name)
    {
        return isset($this->_options[$name]) ? $this->_options[$name] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function __set($name, $value)
    {
        $this->_options[$name] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        foreach ($this->generators as $id => $config) {
            $this->generators[$id] = Yii::createObject($config);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createAction($id)
    {
        /** @var $action GenerateAction */
        $action = parent::createAction($id);
        foreach ($this->_options as $name => $value) {
            $action->generator->$name = $value;
        }

        return $action;
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        $actions = [];
        foreach ($this->generators as $name => $generator) {
            $actions[$name] = [
                'class' => 'yii\gii\console\GenerateAction',
                'generator' => $generator,
            ];
        }

        return $actions;
    }

    public function actionIndex()
    {
//        $this->run('/help', ['default']);
    }

    /**
     * {@inheritdoc}
     */
    public function getUniqueID()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function options($id)
    {
        $options = parent::options($id);
        $options[] = 'overwrite';

        if (!isset($this->generators[$id])) {
            return $options;
        }

        $attributes = $this->generators[$id]->attributes;
        unset($attributes['templates']);

        return array_merge(
            $options,
            array_keys($attributes)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getActionHelpSummary($action)
    {
        if ($action instanceof InlineAction) {
            return parent::getActionHelpSummary($action);
        }

        /* @var $action GenerateAction */
        return $action->generator->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getActionHelp($action)
    {
        if ($action instanceof InlineAction) {
            return parent::getActionHelp($action);
        }

        /** @var $action GenerateAction */
        $description = $action->generator->getDescription();

        return wordwrap(preg_replace('/\s+/', ' ', $description));
    }

    /**
     * {@inheritdoc}
     */
    public function getActionArgsHelp($action)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getActionOptionsHelp($action)
    {
        if ($action instanceof InlineAction) {
            return parent::getActionOptionsHelp($action);
        }
        /** @var $action GenerateAction */
        $attributes = $action->generator->attributes;
        unset($attributes['templates']);
        $hints = $action->generator->hints();

        $options = parent::getActionOptionsHelp($action);
        foreach ($attributes as $name => $value) {
            $type = gettype($value);
            $options[$name] = [
                'type' => $type === 'NULL' ? 'string' : $type,
                'required' => $value === null && $action->generator->isAttributeRequired($name),
                'default' => $value,
                'comment' => isset($hints[$name]) ? $this->formatHint($hints[$name]) : '',
            ];
        }

        return $options;
    }

    protected function formatHint($hint)
    {
        $hint = preg_replace('%<code>(.*?)</code>%', '\1', $hint);
        $hint = preg_replace('/\s+/', ' ', $hint);

        return wordwrap($hint);
    }
}
