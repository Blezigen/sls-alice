<?php
/**
 * This is the template for generating a module class file.
 */

/* @var $className string */
/* @var $this yii\web\View */
/* @var $models array */
/* @var $generator yii\gii\generators\module\Generator */

echo "<?php\n";
?>

namespace <?php echo "$ns\\$moduleID"; ?>;

/**
 * <?php echo $generator->moduleID; ?> module definition class
 */
class <?php echo $className; ?> extends \common\AbstractModule
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = '<?php echo $generator->getControllerNamespace(); ?>';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
    }

    /**
    * @inheritdoc
    */
    public function routes($moduleID)
    {
        $moduleID = $this->id;
        return [
<?php foreach ($models as $model) { ?>
            //<editor-fold desc="<?php echo $model['tableName']; ?>">
            "PUT,PATCH <?php echo $model['tableName']; ?>/<id:\d+>" => "{$moduleID}/<?php echo lcfirst($model['className']); ?>/update",
            "DELETE <?php echo $model['tableName']; ?>/<id:\d+>"    => "{$moduleID}/<?php echo lcfirst($model['className']); ?>/delete",
            "GET,HEAD <?php echo $model['tableName']; ?>/<id:\d+>"  => "{$moduleID}/<?php echo lcfirst($model['className']); ?>/view",
            "POST <?php echo $model['tableName']; ?>"               => "{$moduleID}/<?php echo lcfirst($model['className']); ?>/create",
            "GET,HEAD <?php echo $model['tableName']; ?>"           => "{$moduleID}/<?php echo lcfirst($model['className']); ?>/index",
            "OPTIONS <?php echo $model['tableName']; ?>/<id:\d+>"   => "{$moduleID}/<?php echo lcfirst($model['className']); ?>/options",
            "OPTIONS <?php echo $model['tableName']; ?>"            => "{$moduleID}/<?php echo lcfirst($model['className']); ?>/options",
            //</editor-fold>
<?php } ?>

        ];
    }
}
