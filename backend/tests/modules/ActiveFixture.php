<?php

namespace tests\modules;

use common\models\Collection;
use Symfony\Component\Console\Helper\ProgressBar;

class ActiveFixture extends \yii\test\ActiveFixture
{
    public $collections = null;

    public $output = null;

    public function load()
    {
        $progressBar = new ProgressBar($this->output);
        $progressBar->setFormat('file');
        $progressBar->setMessage(static::class);
//        echo str_pad(static::class, 50, "_");
        try {
            $this->data = [];
            $table = $this->getTableSchema();
            $data = $this->getData();
            $dataCount = count($data);
            $progressBar->setMaxSteps($dataCount);

            foreach ($data as $alias => $row) {
                if (method_exists($this, "afterInsert")){
                    $this->afterInsert($row);
                }
                $primaryKeys = $this->db->schema->insert($table->fullName, $row);
//                $this->data[$alias] = array_merge($row, $primaryKeys);
                $progressBar->advance();
            }
            unset($data);
        } catch (\Throwable $e){
            dd(static::class, $e->getMessage());
        }
        $progressBar->finish();

//        echo " dataCount: ".count($this->data).PHP_EOL;
    }

    public function afterInsert( &$row )
    {
        if (is_array($row)) {
            foreach ($row as $key => $value) {
                if (str_contains($key, "_cid") && is_array($value)) {
                    $collections = $this->getCollections();
                    $collection = array_key_first($value);
                    $slug = $value[$collection];

                    try {
                        $row[$key] = $collections[$collection][$slug];
                    } catch (\Throwable $ex) {
                        dd($key, $value, $collections, $collection, $slug);
                    }
                }
            }
        }
    }

    private function getCollections()
    {
        if ($this->collections === null) {
            $collections = Collection::find()->all();
            foreach ($collections as $collection) {
                $this->collections[$collection->collection][$collection->slug]
                    = $collection->id;
            }
        }

        return $this->collections;
    }
}