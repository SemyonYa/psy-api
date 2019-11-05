<?php

namespace app\controllers;

use app\models\Good;
use app\models\Specialist;
use Yii;
use yii\helpers\Json;
use yii\web\ForbiddenHttpException;

class GoodController extends ManagerController
{
    public function actionAll()
    {
        $spec_id = Yii::$app->request->get('specialistId');
        return Json::encode(
            Good::find()
                ->where(['specialist_id' => $spec_id])
                ->asArray()
                ->all()
        );
    }

    public function actionOne($id)
    {
        return Json::encode(Good::find()->where(['id' => $id])->asArray()->one());

    }

    public function actionCreate()
    {
        $req = Yii::$app->request;
        $spec_id = $req->post('specialistId');
        $this->checkSpecialist($spec_id);

        $good = new Good();
        $good->name = $req->post('name');
        $good->description = $req->post('description');
        $good->price = $req->post('price');
        $good->duration = $req->post('duration');
        $good->specialist_id = $spec_id;
        $good->invisible = $req->post('invisible');
        if ($good->save()) {
            return Json::encode($good->id);
        }
        return Json::encode(false);
    }

    public function actionUpdate()
    {
        $req = Yii::$app->request;
        $spec_id = $req->post('specialistId');
        $this->checkSpecialist($spec_id);

        $id = $req->post('id');
        $good = Good::findOne($id);
        $good->name = $req->post('name');
        $good->description = $req->post('description');
        $good->price = $req->post('price');
        $good->duration = $req->post('duration');
        $good->specialist_id = $spec_id;
        $good->invisible = $req->post('invisible');
        if ($good->save()) {
            return Json::encode($good->id);
        }
        return Json::encode(false);
    }

    public function actionClone() {
        $req = Yii::$app->request;
        $spec_id = $req->post('specialistId');
        $good_id = $req->post('goodId');

        $current_good = Good::findOne($good_id);
        if ($current_good) {
            $new_good = new Good();
            $new_good->name = $current_good->name . ' (copy)';
            $new_good->description = $current_good->description;
            $new_good->price = $current_good->price;
            $new_good->duration = $current_good->duration;
            $new_good->specialist_id = $spec_id;
            $new_good->invisible = $current_good->invisible;
            if ($new_good->save()) {
                return Json::encode($new_good->id);
            } else {
                return Json::encode(false);
            }
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
