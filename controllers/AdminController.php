<?php

namespace app\controllers;

use app\models\AesToken;
use app\models\User;
use Yii;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\UnauthorizedHttpException;

class AdminController extends Controller
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

    public function afterAction($action, $result)
    {
        $token = Yii::$app->request->headers->get('PsyAuth');
        $user_data = AesToken::decode($token);
        $user = User::findOne(['id' => $user_data['id'], 'access_token' => $token]);
        if (!$user) {
            throw new UnauthorizedHttpException();
        }
        if ($user->role !== 'admin') {
            throw new ForbiddenHttpException();
        }
        return parent::afterAction($action, $result);
    }

    public function actionIndex()
    {
        $qwe = [
            [1, 2, 3],
            [
                2, 3, 4,
                [3, 4, 5],
            ],
        ];
        return Json::encode($qwe);
    }

}
