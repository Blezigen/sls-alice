<?php

namespace console\modules\generator\generators\database_model\schemas;

use yii\db\Expression;
use yii\db\pgsql\ColumnSchema;
use yii\db\pgsql\Schema;
use yii\db\TableSchema;

class PgsqlSchema extends Schema
{
    protected function loadColumnSchema($info)
    {
        $info["is_autoinc"] = $info["is_autoinc"] || $info["column_default"] === "gen_random_uuid()";
        return parent::loadColumnSchema($info); 
    }
}