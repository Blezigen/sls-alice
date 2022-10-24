<?php
/**
 * This is the template for generating the model class of a specified table.
 */

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\model\Generator */
/* @var $tableName string full table name */
/* @var $baseClass string base class name */
/* @var $ns string namespace */
/* @var $className string class name */
/* @var $queryClassName string query class name */
/* @var $tableSchema yii\db\TableSchema */
/* @var $properties array list of properties (property => [type, name. comment]) */
/* @var $labels string[] list of attribute labels (name => label) */
/* @var $rules string[] list of validation rules */
/* @var $relations array list of relations (name => relation declaration) */

echo "<?php\n";
?>

namespace <?php echo $ns; ?>\<?php echo $moduleID; ?>\models\write;

use Yii;

class <?php echo $className; ?>Write extends <?php echo "\\common\\models\\$className\n"; ?>
{
    public static function __docAttributeIgnore()
    {
        return [
            "id",
            "created_at",
            "updated_at"
        ];
    }
}
