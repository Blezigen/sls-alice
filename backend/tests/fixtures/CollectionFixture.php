<?php

namespace tests\fixtures;

use Carbon\Carbon;
use common\models\Collection;
use common\models\DiscountCard;
use tests\modules\ActiveFixture;

class CollectionFixture extends ActiveFixture
{
    public $modelClass = Collection::class;
    public $depends = [];

    protected function getData()
    {
        \Yii::setAlias('@install', '@root/console/modules/install');
        \Yii::setAlias('@install_resources', '@install/resources');

        $collections
            = include \Yii::getAlias('@install_resources/collections/default.php');

        $results = [];

        foreach ($collections as $collection => $values) {
            foreach ($values as $slug => $title) {
                $results[] = [
                    "slug"       => $slug,
                    "collection" => $collection,
                    "title"      => $title,
                    "options"    => null,
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now(),
                ];
            }
        }

        return $results;
    }


}
