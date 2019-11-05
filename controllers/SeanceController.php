<?php

namespace app\controllers;

use app\models\Good;
use app\models\Seance;
use app\models\Specialist;
use app\models\Workday;
use Yii;
use yii\helpers\Json;
use yii\web\ForbiddenHttpException;

class SeanceController extends ManagerController
{
    public function create($date, $time, $price, $duration, $good_id, $seance_status = 1)
    {
        $this->checkExist();
        $seance = new Seance();
        $seance->date = $date;
        $seance->time = $time;
        $seance->duration = $duration;
        $seance->good_id = $good_id;
        $seance->price = $price;
        $seance->seance_status = $seance_status;
        $seance->save();

        return true;
    }

    public function createWorkday($date, $spec_id)
    {
        $currentWorkday = Workday::findOne(['date' => $date, 'specialist_id' => $spec_id]);
        if (!$currentWorkday) {
            $workday = new Workday();
            $workday->specialist_id = $spec_id;
            $workday->date = $date;
            $workday->save();
        }
    }

    public function checkWorkday($date, $spec_id)
    {
        $good_ids = $this->getSpecialistGoodIds($spec_id);
        $seance = Seance::find()->where(['date' => $date])->andWhere(['in', 'good_id', $good_ids])->one();
        if ($seance) {
            $this->createWorkday($date, $spec_id);
        } else {
            $wd = Workday::find()->where(['date' => $date, 'specialist_id' => $spec_id])->one();
            if ($wd)
                $wd->delete();
        }
    }

    public function checkExist()
    {

    }

    public function getSpecialistGoodIds($spec_id)
    {
        $goods = Good::find()->where(['specialist_id' => $spec_id])->all();
        $good_ids = [];
        foreach ($goods as $g) {
            $good_ids[] = $g->id;
        }
        return $good_ids;
    }

    public function actionAll()
    {
        $req = Yii::$app->request;
        $spec_id = $req->get('specialistId');
        $goods = Good::find()->where(['specialist_id' => $spec_id])->all();
        $good_ids = [];
        foreach ($goods as $g) {
            $good_ids[] = $g->id;
        }
        $date = $req->get('date');
        $seances = Seance::find()->where(['in', 'good_id', $good_ids])->andWhere(['date' => $date])->orderBy('date')->asArray()->all();

        return Json::encode($seances);
    }

    public function actionOne($id)
    {
        return Json::encode(Seance::find()->where(['id' => $id])->asArray()->one());

    }

    public function actionCreate()
    {
        $req = Yii::$app->request;
        $spec_id = $req->post('specialistId');
        $date = $req->post('date');
        $time = $req->post('time');
        $duration = $req->post('duration');
        $good_id = $req->post('goodId');
        $price = $req->post('price');
        $seance_status = $req->post('seanceStatus');
        if ($this->create($date, $time, $price, $duration, $good_id)) {
            $this->createWorkday($date, $spec_id);
            return Json::encode(true);
        }
        return Json::encode(false);
    }

    public function actionUpdate()
    {
        $req = Yii::$app->request;
        $spec_id = $req->post('specialistId');
        // $this->checkSpecialist($spec_id);

        $id = $req->post('id');
        $seance = Seance::findOne($id);
        $old_date = $seance->date;
        $seance->date = $req->post('date');
        $seance->time = $req->post('time');
        $seance->duration = $req->post('duration');
        $seance->price = $req->post('price');
        $seance->seance_status = $req->post('seanceStatus');
        $seance->good_id = $req->post('goodId');
        if ($seance->save()) {
            $this->checkWorkday($old_date, $spec_id);
            return Json::encode($seance->id);
        }
        return Json::encode(false);
    }

    public function actionDelete()
    {
        $id = Yii::$app->request->post('id');
        $seance = Seance::findOne($id);
        if ($seance->seance_status === 1) {
            if ($seance->delete()) {
                return Json::encode(true);
            }
        }
        return Json::encode(false);
    }

    public function actionClearDate() {
        $req = Yii::$app->request;
        $spec_id = $req->post('specialistId');
        $date = $req->post('date');
        $good_ids = $this->getSpecialistGoodIds($spec_id);
        $seances_on_delete = Seance::find()->where(['date' => $date])->andWhere(['in', 'good_id', $good_ids])->all();
        foreach ($seances_on_delete as $sd) {
            $sd->delete();
        }
        $this->checkWorkday($date, $spec_id);
        return Json::encode(true);
    }

    public function actionWorkdays()
    {
        $req = Yii::$app->request;
        $spec_id = $req->post('specialistId');
        $year = $req->post('year');
        $month = $req->post('month');
        $month = (strlen($month) === 1) ? '0' . $month : $month;
        $start = strtotime($year . '-' . $month . '-01T00:00:00');
        $end = strtotime('-1 day', strtotime('+1 month', $start));
        $workdays = Workday::find()->where(['between', 'date', date('Y-m-d', $start), date('Y-m-d', $end)])->andWhere(['specialist_id' => $spec_id])->all();
        $dates = [];
        foreach ($workdays as $wd) {
            $dates[] = explode('-', $wd->date)[2] * 1;
        }
        return Json::encode($dates);
    }

    public function actionCopyDaySeances()
    {
        $req = Yii::$app->request;
        $date_from = explode('T', $req->post('dateFrom'))[0];
        $spec_id = $req->post('specialistId');
        $date_to = $req->post('dateTo');
        $goods = Good::find()->where(['specialist_id' => $spec_id])->select('id')->all();
        $good_ids = [];
        foreach ($goods as $good) {
            $good_ids[] = $good->id;
        }

        $seances_on_delete = Seance::find()->where(['date' => $date_to])->andWhere(['in', 'good_id', $good_ids])->all();
        foreach ($seances_on_delete as $sd) {
            $sd->delete();
        }

        $current_seances = Seance::find()->where(['date' => $date_from])->andWhere(['in', 'good_id', $good_ids])->all();
        foreach ($current_seances as $s) {
            $this->create($date_to, $s->time, $s->price, $s->duration, $s->good_id);
        }
        $this->createWorkday($date_to, $spec_id);
        return Json::encode(true);
    }

    public function actionShareByWeekday()
    { // -> actionShare
        $req = Yii::$app->request;
        $spec_id = $req->post('specialistId');
        $from = $req->post('dateFrom');
        // $is_weekday = $req->post('isWeekday');
        $start = explode('T', $req->post('start'))[0];
        $end = explode('T', $req->post('end'))[0];
        $date_from = strtotime($from);
        $date_start = strtotime($start);
        $date_end = strtotime($end);

        $goods = Good::find()->where(['specialist_id' => $spec_id])->select('id')->all();
        $good_ids = [];
        foreach ($goods as $good) {
            $good_ids[] = $good->id;
        }

        $current_seances = Seance::find()->where(['date' => $from])->andWhere(['in', 'good_id', $good_ids])->all();

        while ($date_from <= $date_end) {
            if ($date_from > $date_start) {
                $seances_on_delete = Seance::find()->where(['date' => strftime('%Y-%m-%d', $date_from)])->andWhere(['in', 'good_id', $good_ids])->all();
                foreach ($seances_on_delete as $sd) {
                    $sd->delete();
                }
                foreach ($current_seances as $s) {
                    $this->create(strftime('%Y-%m-%d', $date_from), $s->time, $s->price, $s->duration, $s->good_id);
                    $seance = new Seance();
                    $seance->date = strftime('%Y-%m-%d', $date_from);
                    $seance->time = $s->time;
                    $seance->duration = $s->duration;
                    $seance->seance_status = 1;
                    $seance->good_id = $s->good_id;
                    $seance->price = $s->price;
                    $seance->save();
                }
                $this->createWorkday($date_from, $spec_id);
            }
            // if ($is_weekday)
            $date_from = strtotime('+7 days', $date_from);
            // $date_from = strtotime('+1 day', $date_from);
        }

        return Json::encode(true);
    }

    public function actionCheckCreate() // $id = 0

    {
        $req = Yii::$app->request;
        $spec_id = $req->post('specialistId');
        $begin = $req->post('time') . ':00';
        $date = $req->post('date');
        $duration = $req->post('duration');
        $dayFinal = strtotime('+1 day', strtotime($date));
        $goods = Good::find()->where(['specialist_id' => $spec_id])->select('id')->all();
        $good_ids = [];
        foreach ($goods as $good) {
            $good_ids[] = $good->id;
        }
        $seances = Seance::find()->where(['date' => $date])->andWhere(['in', 'good_id', $good_ids])->all();
        foreach ($seances as $seance) {
            $seanceBeginTime = strtotime($date . 'T' . $seance->time);
            $seanceFinalTime = strtotime('+' . $seance->duration . ' minutes', $seanceBeginTime);
            $beginTime = strtotime($date . 'T' . $begin);
            $finalTime = strtotime('+' . $duration . ' minutes', $beginTime);
            $errors = [];
            if ($beginTime >= $seanceBeginTime && $beginTime < $seanceFinalTime) {
                $errors['timeIsBusy'] = true;
            }
            if ($beginTime <= $seanceBeginTime && $finalTime > $seanceBeginTime) {
                $errors['timeIsBusy'] = true;
            }
            if ($finalTime > $dayFinal) {
                $errors['outOfDay'] = true;
            }
            if ($errors) {
                return Json::encode($errors);
            }
        }
        return Json::encode(false);
    }

    public function actionCheckEdit() // $id = 0

    {
        $req = Yii::$app->request;
        $id = $req->post('id');
        $spec_id = $req->post('specialistId');
        $begin = $req->post('time') . ':00';
        $date = $req->post('date');
        $duration = $req->post('duration');
        $dayFinal = strtotime('+1 day', strtotime($date));

        $goods = Good::find()->where(['specialist_id' => $spec_id])->select('id')->all();
        $good_ids = [];
        foreach ($goods as $good) {
            $good_ids[] = $good->id;
        }
        $seances = Seance::find()->where(['date' => $date])->andWhere(['in', 'good_id', $good_ids])->andWhere(['!=', 'id', $id])->all();
        foreach ($seances as $seance) {
            $seanceBeginTime = strtotime($date . 'T' . $seance->time);
            $seanceFinalTime = strtotime('+' . $seance->duration . ' minutes', $seanceBeginTime);
            $beginTime = strtotime($date . 'T' . $begin);
            $finalTime = strtotime('+' . $duration . ' minutes', $beginTime);
            $errors = [];
            if ($beginTime >= $seanceBeginTime && $beginTime < $seanceFinalTime) {
                $errors['timeIsBusy'] = true;
            }
            if ($beginTime <= $seanceBeginTime && $finalTime > $seanceBeginTime) {
                $errors['timeIsBusy'] = true;
            }
            if ($finalTime > $dayFinal) {
                $errors['outOfDay'] = true;
            }
            if ($errors) {
                return Json::encode($errors);
            }
        }
        return Json::encode(false);
    }

    private function checkSpecialist($spec_id)
    {
        $user_id = Yii::$app->request->post('userId');
        $spec = Specialist::find()->where(['id' => $spec_id])->select(['id', 'user_id'])->one();
        if ($spec) {
            if ($spec->user_id != $user_id) {
                throw new ForbiddenHttpException();
            }
        }
    }
}
