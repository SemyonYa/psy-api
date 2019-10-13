<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\helpers\Json;


class AdminController extends Controller 
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
        $qwe = [
            [1, 2, 3],
            [
                2, 3, 4,
                [3, 4, 5] 
            ]
        ];
        return Json::encode($qwe);
    }

}
