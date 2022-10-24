<?php
/**
 * This is the template for generating the model class of a specified table.
 */

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\model\Generator */
/* @var $tableName string full table name */
/* @var $baseClass string base class name */
/* @var $className string class name */
/* @var $queryClassName string query class name */
/* @var $tableSchema yii\db\TableSchema */
/* @var $properties array list of properties (property => [type, name. comment]) */
/* @var $labels string[] list of attribute labels (name => label) */
/* @var $rules string[] list of validation rules */
/* @var $relations array list of relations (name => relation declaration) */

echo "<?php\n";
?>

namespace <?php echo $generator->ns; ?>;

use Yii;

/**
 * This is the model class for table "<?php echo $generator->generateTableName($tableName); ?>".
 *
<?php foreach ($properties as $property => $data) { ?>
 * @property <?php echo "{$data['type']} \${$property}" . ($data['comment'] ? ' ' . strtr($data['comment'], ["\n" => ' ']) : '') . "\n"; ?>
<?php } ?>
 *
 */
class <?php echo $className; ?> extends <?php echo '\\' . ltrim($baseClass, '\\') . "\n"; ?>
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '<?php echo $generator->generateTableName($tableName); ?>';
    }
<?php if ($generator->db !== 'db') { ?>

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('<?php echo $generator->db; ?>');
    }
<?php } ?>

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return array_merge(parent::rules(),[
<?php foreach ($rules as $key => $rule) { ?>
            <?php echo(is_numeric($key) ? '' : "\"$key\" => ") . $rule . ",\n"; ?>
<?php } ?>
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
<?php foreach ($labels as $name => $label) { ?>
            <?php echo "'$name' => " . $generator->generateString($label) . ",\n"; ?>
<?php } ?>
        ];
    }

<?php if ($queryClassName) { ?>
<?php
    $queryClassFullName = ($generator->ns === $generator->queryNs) ? $queryClassName : '\\' . $generator->queryNs . '\\' . $queryClassName;
    echo "\n";
?>
    /**
     * {@inheritdoc}
     * @return <?php echo $queryClassFullName; ?> the active query used by this AR class.
     */
    public static function find()
    {
        return new <?php echo $queryClassFullName; ?>(get_called_class());
    }
<?php } ?>
}
