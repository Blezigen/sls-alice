<?php

namespace tests\generator;

use Closure;
use yii\db\Query;

class Generator
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function generate(int $count = 1): array
    {
        $items = [];
        for ($i = 0; $i < $count; $i++){
            $items[$i] = $this->configure();
        }
        return $items;
    }

    private function configure(): array
    {
        $entity = [];
        foreach ($this->config as $key => $value){
            $this->prepare($entity, $key, $this->configureProperty($value));
        }
        return $entity;
    }

    private function configureProperty($value)
    {
        if($value instanceof Query){
            return $this->generateAssociation($value);
        } else if($value instanceof Closure){
            return $value();
        } else{
            return $value;
        }
    }

    private function generateAssociation(Query $query): ?int
    {
        return (new AssociationHelper($query))->generate();
    }

    private function prepare(array &$items, $key, $value): void
    {
        if(is_array($value)){
            $items = array_merge($items, $value);
        }else{
            $items[$key] = $value;
        }
    }
}