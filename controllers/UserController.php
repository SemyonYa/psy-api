<?php

namespace app\controllers;

use app\models\User;
use Yii;
use yii\web\Controller;
use yii\helpers\Json;


class UserController extends AdminController
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

    public function actionAll()
    {
        return Json::encode(
            User::find()
                ->where(['blocked' => 0])
                ->select(['id', 'organization_name', 'login', 'specialists_quantity', 'parent_user_id', 'blocked'])
                ->asArray()
                ->all()
        );
    }

    public function actionOne($id)
    {
        return Json::encode(User::find()->where(['id' => $id])->asArray()->one());

    }

    public function actionCreate()
    {
        $req = Yii::$app->request;
        $user = new User();
        $user->organization_name = $req->post('organization_name');
        if ($req->post('specialists_quantity')) {
            $user->specialists_quantity = $req->post('specialists_quantity');
        }
        $user->parent_user_id = $req->post('parent_user_id');
        $user->login = $req->post('login');
        if ($user->save()) {
            return Json::encode($user->id);
        }
        return false;
    }

    public function actionUpdate(User $user)
    {

    }

    public function actionDelete($id)
    {
        $user = User::findOne($id);
        $user->blocked = 1;
        if ($user->save()) {
            return true;
        }
        return false;
    }

}
