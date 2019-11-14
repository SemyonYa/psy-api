<?php

namespace app\controllers;

use app\models\User;
use app\models\Specialist;
use app\models\Good;
use app\models\Seance;
use Yii;
use yii\helpers\Json;
use yii\web\Controller;

class ClientController extends Controller
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [
            'corsFilter' => [
                'class' => \yii\filters\Cors::className(),
                'cors' => [
                    // Разрешаем доступ с указанных доменов.
                    'Origin' => ['http://localhost:8100', 'http://localhost:8101', 'http://admin.injini.ru', 'http://manager.injini.ru'],
                    'Access-Control-Allow-Origin' => true,
                    // Куки от кроссдоменного запроса
                    // будут установлены браузером только при заголовке
                    // "Access-Control-Allow-Credentials".
                    'Access-Control-Allow-Credentials' => true,
                    // Разрешаем только метод POST.
                    'Access-Control-Request-Method' => ['POST'],
                    'Access-Control-Allow-Headers' => ['Origin', 'Content-Type', 'X-Auth-Token', 'Authorization', 'PsyAuth'],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionCompanies()
    {
        $users = User::find()->asArray()->all();
        return Json::encode($users);
    }

    public function actionCompany($id)
    {
        $user = User::findOne($id);
        return Json::encode($user);
    }

    public function actionSpecialists($companyId)
    {
        return Json::encode(Specialist::find()->where(['user_id' => $companyId])->all());
    }

    public function actionSpecialist($id)
    {
        return Json::encode(Specialist::findOne($id));
    }

    public function actionWorkdays($spec_id, $year, $month)
    {
        $month = (strlen($month) === 1) ? '0' . $month : $month;
        $start = strtotime($year . '-' . $month . '-01T00:00:00');
        $end = strtotime('-1 day', strtotime('+1 month', $start));
        $good_ids = Good::find()->where(['specialist_id' => $spec_id])->select(['id'])->column();

        $workdays = Seance::find()->where(['between', 'date', date('Y-m-d', $start), date('Y-m-d', $end)])->andWhere(['in', 'good_id', $good_ids])
            ->select(['date'])->distinct()->column();
        $dates = [];
        foreach ($workdays as $wd) {
            $dates[] = explode('-', $wd->date)[2] * 1;
        }
        return Json::encode($dates);
    }

    public function actionGoodWorkdays($good_id, $year, $month)
    {
        $month = (strlen($month) === 1) ? '0' . $month : $month;
        $start = strtotime($year . '-' . $month . '-01T00:00:00');
        $end = strtotime('-1 day', strtotime('+1 month', $start));
        $workdays = Seance::find()->where(['between', 'date', date('Y-m-d', $start), date('Y-m-d', $end)])->andWhere(['good_id' => $good_id])
            ->select(['date'])->distinct()->column();
        $dates = [];
        foreach ($workdays as $wd) {
            $dates[] = explode('-', $wd)[2] * 1;
        }
        return Json::encode($dates);
    }

    public function actionGoods($spec_id)
    {
        return Json::encode(Good::find()->where(['specialist_id' => $spec_id])->all());
    }

    public function actionGood($id)
    {
        return Json::encode(Good::findOne($id));
    }

    public function actionGoodSeances($good_id, $date)
    {
        return Json::encode(Seance::find()->where(['date' => $date, 'good_id' => $good_id])->orderBy('date')->asArray()->all());
    }

    public function actionSeances($spec_id, $date)
    {
        $good_ids = Good::find()->where(['specialist_id' => $spec_id])->select(['id'])->column();
        $seances = Seance::find()->where(['in', 'good_id', $good_ids])->andWhere(['date' => $date])->orderBy('date')->asArray()->all();
        return Json::encode($seances);
    }

    public function actionSeance($id)
    {
        $q = 'SELECT s.date, s.time, s.duration, s.seance_status, s.price, g.id, g.name FROM seance as s INNER JOIN good as g ON s.good_id = g.id WHERE s.id = :id';
        $seance = Yii::$app->db->createCommand($q)->bindParam(':id', $id)->queryOne();
        return Json::encode($seance);
    }

    public function actionBooking()
    {
        return Json::encode(true);
    }
}
