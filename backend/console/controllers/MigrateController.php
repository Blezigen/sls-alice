<?php

namespace console\controllers;

class MigrateController extends \yii\console\controllers\MigrateController
{
    /**
     * Determines whether the error message is related to deleting a view or not
     *
     * @param string $errorMessage
     *
     * @return bool
     */
    private function isViewRelated($errorMessage)
    {
        $dropViewErrors = [
            'DROP VIEW to delete view', // SQLite
            'SQLSTATE[42S02]', // MySQL
        ];

        foreach ($dropViewErrors as $dropViewError) {
            if (strpos($errorMessage, $dropViewError) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @since 2.0.13
     */
    protected function truncateDatabase()
    {
        $db = $this->db;
        $schemas = $db->schema->getTableSchemas();

        // First drop all foreign keys,
        foreach ($schemas as $schema) {
            foreach ($schema->foreignKeys as $name => $foreignKey) {
                $db->createCommand()->dropForeignKey($name, $schema->name)->execute();
                $this->stdout("Foreign key $name dropped.\n");
            }
        }

        // Then drop the tables:
        foreach ($schemas as $schema) {
            try {
                $command = $db->createCommand()->dropTable($schema->name);
                $command->sql .= ' CASCADE';
                $command->execute();
                $this->stdout("Table {$schema->name} dropped.\n");
            } catch (\Exception $e) {
                if ($this->isViewRelated($e->getMessage())) {
                    $db->createCommand()->dropView($schema->name)->execute();
                    $this->stdout("View {$schema->name} dropped.\n");
                } else {
                    $this->stdout("Cannot drop {$schema->name} Table .\n");
                }
            }
        }
    }
}
