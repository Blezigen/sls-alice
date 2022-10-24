<?php
/**
 * This is the template for generating the ActiveQuery class.
 */

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\model\Generator */
/* @var $tableName string full table name */
/* @var $className string class name */
/* @var $ns string namespace */
/* @var $tableSchema yii\db\TableSchema */
/* @var $labels string[] list of attribute labels (name => label) */
/* @var $rules string[] list of validation rules */
/* @var $relations array list of relations (name => relation declaration) */
/* @var $className string class name */
/* @var $modelClassName string related model class name */

$modelFullClassName = $modelClassName;
if ($generator->ns !== $generator->queryNs) {
    $modelFullClassName = '\\' . $generator->ns . '\\' . $modelFullClassName;
}

echo "<?php\n";
?>

namespace <?php echo $ns; ?>;

/**
 * This is the ActiveQuery class for [[<?php echo $modelFullClassName; ?>]].
 *
 * @see <?php echo $modelFullClassName . "\n"; ?>
 */
class <?php echo $className; ?> extends <?php echo '\\' . ltrim($generator->queryBaseClass, '\\') . "\n"; ?>
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return <?php echo $modelFullClassName; ?>[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return <?php echo $modelFullClassName; ?>|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
