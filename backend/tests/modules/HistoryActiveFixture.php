<?php

namespace tests\modules;

use common\models\Collection;
use common\models\Ship;
use Symfony\Component\Console\Helper\ProgressBar;
use yii\test\ActiveFixture;

abstract class HistoryActiveFixture extends \tests\modules\ActiveFixture
{


    public function beforeUnload()
    {
        $tableName = $this->modelClass::tableName();
        \Yii::$app->db->createCommand()
            ->setSql("ALTER TABLE $tableName DISABLE TRIGGER \"TRG_HISTORY\";")
            ->execute();
        parent::beforeUnload();
    }

    public function afterUnload()
    {
        $tableName = $this->modelClass::tableName();
        \Yii::$app->db->createCommand()
            ->setSql("ALTER TABLE $tableName ENABLE TRIGGER \"TRG_HISTORY\";")
            ->execute();
        parent::afterUnload();
    }

    /**
     * Toggles the DB integrity check.
     * @param bool $check whether to turn on or off the integrity check.
     */
    public function checkIntegrity($check)
    {
        if (!$this->db instanceof \yii\db\Connection) {
            return;
        }
        foreach ($this->schemas as $schema) {

            $enable = $check ? 'ENABLE' : 'DISABLE';
            $schema = $schema ?: $this->db->getSchema()->defaultSchema;
            $tableNames = $this->db->getSchema()->getTableNames($schema);
            $command = '';

            foreach ($tableNames as $tableName) {
                if ($tableName === "collection")
                    continue;
                $tableName = $this->db->quoteTableName("{$schema}.{$tableName}");
                $command .= "ALTER TABLE $tableName $enable TRIGGER \"TRG_HISTORY\"; ";
            }

            // enable to have ability to alter several tables
            $this->db->getMasterPdo()->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);

            return $command;

//            $this->db->createCommand()->checkIntegrity($check, $schema)->execute();
        }
    }

}
