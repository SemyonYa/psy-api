<?php

namespace app\controllers;

use app\models\Specialist;
use Yii;
use yii\helpers\Json;
use yii\web\Controller;


class SiteController extends Controller 
{
    public function behaviors()
    {
        return [];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionQwerty() {
        return Json::encode((Specialist::find()->where(['id' => 3])->select(['id', 'user_id'])->one())->user_id);
    }

}
