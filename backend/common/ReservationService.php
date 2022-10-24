<?php

namespace common;

use Carbon\Carbon;
use common\components\services\ShipService;
use common\exceptions\ValidationException;
use common\models\Cabin;
use common\models\Collection;
use common\models\Contractor;
use common\models\Ship;
use common\models\Tour;
use common\models\Waiting;
use common\modules\sender\contracts\ISenderInitiator;
use common\modules\sender\job\NotificationJob;
use common\modules\sender\providers\email\contracts\IEmailInitiator;
use common\modules\sender\SingleReceiver;
use common\notifications\FreeCabinNotification;
use common\notifications\ListWaitingNotification;
use Yii;
use yii\base\Model;

class ReservationService extends Model
{
    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    public function notifyCabinFree(Cabin $cabin, Tour $tour, Ship $ship = null)
    {
        $waiting = Waiting::find()->andWhere([
            "AND",
            ["tour_id" => $tour->id],
            ["cabin_id" => $cabin->id],
        ])->all();

        foreach ($waiting as $waiting){
            Yii::$app->queue->push(new NotificationJob(
                initiator: new FreeCabinNotification($cabin),
                receiver: new SingleReceiver([
                    "email" => $waiting->contractor->email,
                ])
            ));
            $waiting->delete();
        }
    }

    public function addWaitingList($tourId, $number, $contractorId, $dateEndDT, $save)
    {
        $shipService = Yii::$container->get(ShipService::class);
        $tourService = Yii::$container->get(TourService::class);
        $tour = $tourService->getTour($tourId);

        $cabin = $shipService->getCabinByNumber($tour->ship_id, $number);
        $temp = new Waiting([
            'tour_id' => $tour->id,
            'contractor_uid' => $contractorId,
            'cabin_id' => $cabin->id,
            'waiting_end_dt' => $dateEndDT,
        ]);
        if (!$temp->validate()) {
            throw new ValidationException($temp->errors);
        }

        if (!$save) {
            return [
                'message' => 'Добавление в лист ожидания',
                'data' => $temp->toArray(),
            ];
        }

        $temp->save();

        return [
            'message' => 'Добавлен в лист ожидания',
            'data' => $temp,
        ];
    }

    public function dropWaiting($id, $save = false)
    {
        /** @var Waiting $waiting */
        $waiting = Waiting::find()->byId($id)->one();

        if (!$waiting) {
            throw new \Exception(Yii::t("app", "{entity} not found", [
                "entity" => Yii::t("app", "Waiting")
            ]));
        }

        if ($save) {
            $waiting->delete();

            $recipients = Contractor::find()
                ->leftJoin('waiting', 'waiting.contractor_uid = contractors.id')
                ->andWhere(['waiting.cabin_id' => $waiting->cabin_id])
                ->andWhere(['waiting.tour_id' => $waiting->tour_id])
                ->andWhere(['>', 'waiting.waiting_end_dt', Carbon::now()])
                ->all();

            Yii::$app->notifier->send($recipients, new ListWaitingNotification($waiting));
        }

        return [
            "message" => "Успешно удалено",
            "data" => $id
        ];
    }
}
