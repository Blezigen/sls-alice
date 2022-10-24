<?php

namespace console;

class Migration extends \yii\db\Migration
{
    public function serial()
    {
        return 'serial primary key';
    }

    public function collectionForeignKey($table, $fieldName)
    {
        $tableName = str_replace('{', '', $table);
        $tableName = str_replace('}', '', $tableName);

        $this->createIndex("IDX_{$tableName}_{$fieldName}", $table, [$fieldName]);
        $this->addForeignKey(
            "fk_{$tableName}_{$fieldName}",
            $table,
            [$fieldName],
            '{{%collections}}',
            ['id'],
            'NO ACTION',
            'NO ACTION'
        );
    }

    public function createTrigger($name, $table, $procedure, $type = 'INSERT OR UPDATE', $BEFORE_OR_AFTER = 'BEFORE')
    {
        $this->execute("CREATE TRIGGER \"$name\" $BEFORE_OR_AFTER $type ON \"$table\" FOR EACH ROW EXECUTE PROCEDURE \"public\".\"$procedure\"()");
    }

    public function disableDeleteQuery($table)
    {
//        $name = strtoupper("CHK_DELETE_$table");
//        $this->execute("CREATE RULE $name AS ON DELETE TO $table DO INSTEAD NOTHING;;");
    }

    public function createView($name, $query)
    {
        $time = $this->beginCommand("create view $name");
        $this->db->createCommand()->createView($name, $query)->execute();
        $this->endCommand($time);
    }

    public function dropView($name)
    {
        $time = $this->beginCommand("drop view $name");
        $this->db->createCommand()->dropView($name)->execute();
        $this->endCommand($time);
    }

    public function enableHistory($table)
    {
        $time = $this->beginCommand("CALL enable_history('$table')");
        $this->db->createCommand("CALL enable_history('$table')")->execute();
        $this->endCommand($time);
    }

    public function disableHistory($table)
    {
        $time = $this->beginCommand("CALL disable_history('$table')");
        $this->db->createCommand("CALL disable_history('$table')")->execute();
        $this->endCommand($time);
    }
}
