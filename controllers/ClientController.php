<?php

namespace app\controllers;

use app\models\Client;
use app\models\Good;
use app\models\Seance;
use app\models\Specialist;
use app\models\User;
use app\services\MailSender;
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
        $users = User::find()->where(['parent_user_id' => 0])->asArray()->all();
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
        return Json::encode(Seance::find()->where(['date' => $date, 'good_id' => $good_id, 'status' => 0])->orderBy('date')->asArray()->all());
    }

    public function actionSeances($spec_id, $date)
    {
        $good_ids = Good::find()->where(['specialist_id' => $spec_id])->select(['id'])->column();
        $seances = Seance::find()->where(['in', 'good_id', $good_ids])->andWhere(['date' => $date])->orderBy('date')->asArray()->all();
        return Json::encode($seances);
    }

    public function actionSeance($id)
    {
        $q = 'SELECT s.id, s.date, s.time, s.duration, s.status, s.price, s.good_id, g.name FROM seance as s LEFT JOIN good as g ON s.good_id = g.id WHERE s.id = :id';
        $seance = Yii::$app->db->createCommand($q)->bindParam(':id', $id)->queryOne();
        return Json::encode($seance);
    }

    public function actionPreBooking()
    {
        $req = Yii::$app->request;
        $seance_id = $req->post('seanceId');
        $email = $req->post('email');
        $phone = $req->post('phone');

        $seance = Seance::findOne($seance_id);
        $res;
        if ($seance->status === 0 && $seance->booking_now === 0) {
            if ($seance->booking_start_at === null) {
                $res = $this->preBooking($seance, $email, $phone);
            } else if (strtotime('+10 minutes', strtotime($seance->booking_start_at)) < mktime()) {
                $res = $this->preBooking($seance, $email, $phone);
            }
        }
        if ($res) {
            return Json::encode(true);
        }
        return Json::encode('busy');
    }

    public function preBooking(Seance $seance, $email, $phone)
    {
        $seance->booking_now = 10;
        $seance->booking_start_at = date('Y-m-d H:i:s');
        $booking_code = rand(100000, 999999);
        $seance->booking_code = $booking_code;
        MailSender::sendMsg($email, $booking_code);

        return $seance->save();
    }

    public function actionBooking()
    {
        $req = Yii::$app->request;
        $seance_id = $req->post('seanceId');
        $code = $req->post('code');

        $seance = Seance::findOne(['id' => $seance_id]);
        
        if ($seance) {
            if ($seance->booking_code != $code) {
                return Json::encode('code');
            }
            $client = new Client();
            $client->name = $req->post('clientName');
            $client->phone = $req->post('clientPhone');
            $client->email = $req->post('clientEmail');
            $client->wish = $req->post('clientWish');
            $client->seance_id = $seance_id;
            if ($client->save()) {
                $seance->status = 2;
                $seance->client_id = $client->id;
                if ($seance->save()) {
                    return Json::encode(true);
                }
            }
        }

        return Json::encode(false);
    }

}
