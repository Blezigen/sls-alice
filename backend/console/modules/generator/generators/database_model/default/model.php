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

namespace <?php echo $ns; ?>;

use Yii;

/**
 * This is the model class for table "<?php echo $generator->generateTableName($tableName); ?>".
 *
<?php if (!empty($relations)) { ?>
<?php foreach ($relations as $name => $relation) { ?>
 * @property <?php echo $relation[1] . ($relation[2] ? '[]' : '') . ' $' . lcfirst($name) . "\n"; ?>
<?php } ?>
<?php } ?>
 */
class <?php echo $className; ?> extends <?php echo '\\' . ltrim($baseClass, '\\') . "\n"; ?>
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return array_merge(parent::rules(),[]);
    }

    /**
    * {@inheritdoc}
    */
    public function extraFields()
    {
        return array_merge(parent::extraFields(), [
<?php if (!empty($relations)) { ?>
<?php foreach ($relations as $name => $relation) { ?>
            "<?php echo \yii\helpers\Inflector::camel2id($name, '_', true); ?>" => function() {
                return $this-><?php echo lcfirst($name); ?>;
            },
<?php } ?>
<?php } ?>
        ]);
    }


    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),[
<?php foreach ($labels as $name => $label) { ?>
            <?php echo "//'$name' => " . $generator->generateString($label) . ",\n"; ?>
<?php } ?>
        ]);
    }
<?php foreach ($relations as $name => $relation) { ?>

    /**
     * Gets query for [[<?php echo $name; ?>]].
     *
     * @return <?php echo $relationsClassHints[$name] . "\n"; ?>
     */
    public function get<?php echo $name; ?>()
    {
        <?php echo $relation[0] . "\n"; ?>
    }
<?php } ?>
}
