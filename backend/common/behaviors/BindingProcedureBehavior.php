<?php

namespace common\behaviors;

use yii\base\Behavior;

class BindingProcedureBehavior extends Behavior
{
    /**
     * Название процедуры или функции
     */
    public string $name;

    /**
     * Правила биндинга аргументов в процедуру
     */
    public array $rules;

    public function executeBinding($scalar = false)
    {
        $entity = $this->owner;
        $args = array_keys($this->rules);
        $args = array_map(function ($data) {
            return ":$data";
        }, $args);
        $args = implode(',', $args);

        $command = \Yii::$app->getDb()->createCommand("CALL \"{$this->name}\"($args)");

        foreach ($this->rules as $paramKey => $valueKey) {
            if ($valueKey instanceof \Closure) {
                $valueKey = $valueKey();
                $command->bindValue($paramKey, $valueKey);
            } elseif (is_string($valueKey)) {
                $command->bindValue($paramKey, $entity->$valueKey);
            }
        }

        if ($scalar) {
            return $command->queryScalar();
        }

        if ($command->execute() != 0) {
            return false;
        }

        return true;
    }
}
