<?php

namespace tests\generator;

use yii\db\Query;

class AssociationHelper
{
    private array $items;

    public function __construct(Query $query)
    {
        $this->items = $query->select('id')->column();
    }

    public function generate(): ?int
    {
        $count = count($this->items);

        if($count == 0){
            return null;
        }

        return $this->items[mt_rand(0, $count - 1)];
    }

    public static function create(Query $query): AssociationHelper
    {
        return new AssociationHelper($query);
    }
}