<?php

namespace console;

use Carbon\Carbon;

abstract class MigrationSProcedure extends \yii\db\Migration
{
    protected string $lastChanges = 'actual';
    protected string $procedureName;

    public function getLastChanges()
    {
        return Carbon::parse($this->lastChanges)->timestamp;
    }

    public function getName()
    {
        return $this->procedureName;
    }

    public function getHash()
    {
        return md5($this->lastChanges);
    }

    public function getArgument()
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

    public function up()
    {
        $name = $this->getName();
        $args = $this->getArgument();
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
        $dropCommand = \Yii::$app->db->createCommand("DROP PROCEDURE IF EXISTS $name($dropCommandArgs)");
        $dropCommand->execute();

        $lang = 'plpgsql';

        $sql = "CREATE OR REPLACE PROCEDURE {$name}({$args}) LANGUAGE {$lang} AS $$ DECLARE {$declare} BEGIN {$body} END; $$;";
        $command = \Yii::$app->db->createCommand($sql);
        $command->execute();

        return true;
    }

    public function down()
    {
        return false;
    }
}
