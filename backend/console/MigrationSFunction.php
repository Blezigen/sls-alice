<?php

namespace console;

use Carbon\Carbon;

abstract class MigrationSFunction extends \yii\db\Migration
{
    protected string $lastChanges = 'actual';
    protected string $functionName;
    protected string $lang = 'plpgsql';

    public function getLastChanges()
    {
        return Carbon::parse($this->lastChanges)->timestamp;
    }

    public function getName()
    {
        return $this->functionName;
    }

    public function getArgument()
    {
        return '';
    }

    public function getReturn()
    {
        return '';
    }

    public function getBody()
    {
        return '';
    }

    public function getDeclare()
    {
        return '';
    }

    public function getHash()
    {
        return md5($this->lastChanges);
    }

    public function up()
    {
        $name = $this->getName();
        $args = $this->getArgument();
        $return = $this->getReturn();
        $body = $this->getBody();
        $declare = $this->getDeclare();

        if (!empty($declare)) {
            $declare = "$declare;";
        }

        if (!empty($args)) {
            $dropCommandArgs = explode(',', $args);
            $dropCommandArgs = array_map(function ($arg) {
                $data = explode(' ', $arg);

                return $data[array_key_last($data)];
            }, $dropCommandArgs);
            $dropCommandArgs = implode(',', $dropCommandArgs);
        }
        $dropCommand = \Yii::$app->db->createCommand("DROP FUNCTION IF EXISTS $name($dropCommandArgs)");
        $dropCommand->execute();

        $lang = $this->lang;

        $sql = "CREATE OR REPLACE FUNCTION {$name}({$args}) RETURNS {$return} LANGUAGE {$lang} AS $$ DECLARE {$declare} BEGIN {$body} END; $$;";
        $command = \Yii::$app->db->createCommand($sql);
        $command->execute();

        return true;
    }

    public function down()
    {
        return false;
    }
}
