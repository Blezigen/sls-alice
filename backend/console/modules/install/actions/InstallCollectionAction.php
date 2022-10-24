<?php

namespace console\modules\install\actions;

use common\models\Collection;
use console\AbstractConsoleController;
use yii\base\Action;

/**
 * @property AbstractConsoleController $controller
 */
class InstallCollectionAction extends Action
{
    public $resources = [];

    public function initElemet($collectionArray, $only = null)
    {
        foreach ($collectionArray as $collectionKey => $collectionElements) {
            $count = count($collectionElements);
            $iterated = 1;
            $this->controller->consoleLog("├■ Collection: <number>$collectionKey</number> (<number>$count</number>):");
            if ($only !== null && $collectionKey !== $only) {
                $this->controller->consoleLog(" <error>Пропуск не равен `$only`</error>", true);
                continue;
            } elseif ($only !== null && $collectionKey === $only) {
                $this->controller->consoleLog(" <value>Найден `$only`</value>", true);
            } else {
                $this->controller->consoleLog('', true);
            }
            foreach ($collectionElements as $collectionSlug => $collectionData) {
                $collectionTitle = $collectionData;
                $meta = [];
                if (is_array($collectionData)) {
                    $collectionSlug = $collectionData['slug'];
                    $collectionTitle = $collectionData['title'];
                    $meta = $collectionData['_meta'];
                }
                $this->controller->consoleLog('├');

                if (!empty($meta)) {
                    $this->controller->consoleLog('┬');
                }

                $this->controller->consoleLog("■ Item ($count/$iterated): <number>$collectionSlug</number>");

                $collectionModel = Collection::find()->andWhere([
                    'collection' => $collectionKey,
                    'slug' => $collectionSlug,
                    //                        "is_dictionary" => 1
                ])->one();
                if ($collectionModel === null) {
                    $collectionModel = new Collection();
                    $collectionModel->collection = $collectionKey;
                    $collectionModel->slug = $collectionSlug;
                }

                $collectionModel->title = $collectionTitle;
                $collectionModel->save();
                $id = $collectionModel->id;
                $this->controller->consoleLog("( id:<number>{$id}</number> ) - <value>Success</value>", true);
                foreach ($meta as $metaKey => $metaValue) {
                    $this->controller->consoleLog("│├□ Meta <number>$metaKey</number>: <number>$metaValue</number> - ");
                    try {
                        $collectionModel->setMeta($metaKey, $metaValue);
                        $this->controller->consoleLog('<value>Success</value>', true);
                    } catch (\Throwable $ex) {
                        $this->controller->consoleLog("<error>{$ex->getMessage()},{$ex->getFile()}:{$ex->getLine()}</error>", true);
                        throw $ex;
                    }
                }
                ++$iterated;
            }
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
        $this->controller->consoleLog('<value>Словари загружены!</value>', true);
    }
}
