<?php

namespace app\controllers;

use app\models\Specialist;
use app\models\User;
use Yii;
use yii\helpers\Json;

class SpecialistController extends ManagerController
{
    public function actionAll()
    {
        $user_id = Yii::$app->request->get('userId');
        return Json::encode(
            Specialist::find()
                ->where(['user_id' => $user_id])
                ->asArray()
                ->all()
        );
    }

    public function actionOne($id)
    {
        return Json::encode(Specialist::find()->where(['id' => $id])->asArray()->one());

    }

    public function actionCreate()
    {
        $req = Yii::$app->request;
        $spec = new Specialist();
        $spec->name = $req->post('name');
        $spec->description = $req->post('description');
        $spec->user_id = $req->post('userId');
        if ($spec->save()) {
            return Json::encode($spec->id);
        }
        return Json::encode(false);
    }

    public function actionUpdate()
    {
        $req = Yii::$app->request;
        $id = $req->post('id');
        $spec = Specialist::findOne($id);
        $spec->name = $req->post('name');
        $spec->description = $req->post('description');
        if ($spec->save()) {
            return Json::encode($spec->id);
        }
        return Json::encode(false);
    }

    public function actionCheckCreateName() {
        $req = Yii::$app->request;
        $user_id = $req->post('userId');
        $name = $req->post('name');

        $specialist = Specialist::findOne(['user_id' => $user_id, 'name' => $name]);
        if ($specialist) {
            return Json::encode(['nameIsUsed' => true]);
        } else {
            return Json::encode(null);
        }

    }

    public function actionCheckEditName() {
        $req = Yii::$app->request;
        $user_id = $req->post('userId');
        $name = $req->post('name');
        $id = $req->post('id');

        $specialist = Specialist::find()->where(['user_id' => $user_id, 'name' => $name])->andWhere(['!=', 'id', $id])->one();
        if ($specialist) {
            return Json::encode(['nameIsUsed' => true]);
        } else {
            return Json::encode(null);
        }

    }

    // public function actionDelete()
    // {
    //     $id = Yii::$app->request->post('id');
    //     $user = User::findOne($id);
    //     $user->blocked = 1;
    //     if ($user->save()) {
    //         return true;
    //     }
    //     return false;
    // }

}
