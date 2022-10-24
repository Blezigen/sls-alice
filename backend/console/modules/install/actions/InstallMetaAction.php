<?php

namespace console\modules\install\actions;

use common\models\MetaCommonField;
use console\AbstractConsoleController;
use yii\base\Action;

/**
 * @property AbstractConsoleController $controller
 */
class InstallMetaAction extends Action
{
    public $resources = [];

    public function initElemet($metaArray, $only = null)
    {
        foreach ($metaArray as $metaAttribute => $metaFieldData) {
            $this->controller->consoleLog("├■ Meta: <number>$metaAttribute</number>: ");
            if ($only !== null && $metaAttribute !== $only) {
                $this->controller->consoleLog(' <error>пропуск</error>', true);
                continue;
            }

            $metaFieldModel = MetaCommonField::find()->andWhere([
                'attribute' => $metaAttribute,
            ])->one();

            $operation = 'Обновлено!';
            if ($metaFieldModel === null) {
                $metaFieldModel = new MetaCommonField();
                $metaFieldModel->attribute = $metaAttribute;
                $operation = 'Создано!';
            }
            $metaFieldModel->title = $metaFieldData['title'];
            $metaFieldModel->type = $metaFieldData['type'];
            $metaFieldModel->setRule($metaFieldData['rule']);
            $metaFieldModel->save();

            $this->controller->consoleLog("<value>$operation</value>", true);
        }
    }

    public function run($collection = null)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            foreach ($this->resources as $collectionFile) {
                $collections = include realpath(\Yii::getAlias($collectionFile));
                $maxCount = count($collections);
                $this->controller->consoleLog("Установка файла коллекции: <number>$collectionFile</number> (<number>$maxCount</number>):", true);
                $this->initElemet($collections, $collection);
                unset($collections);
            }
            $transaction->commit();
        } catch (\Throwable $ex) {
            $transaction->rollBack();
            $this->controller->consoleLog("<error>{$ex->getMessage()}</error>", true);
        }
        $this->controller->consoleLog('<value>Мета загружена!</value>', true);
    }
}
