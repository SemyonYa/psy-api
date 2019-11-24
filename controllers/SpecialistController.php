<?php

namespace app\controllers;

use app\models\Specialist;
use Imagine\Image\Box;
use Yii;
use yii\helpers\FileHelper;
use yii\helpers\Json;
use yii\imagine\Image;

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

    public function actionCheckCreateName()
    {
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

    public function actionCheckEditName()
    {
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

    public function actionDelete()
    {
        $id = Yii::$app->request->post('id');
        $specialist = Specialist::findOne($id);
        return Json::encode($specialist->delete());
    }

    public function actionLoadPhoto()
    {
        $req = Yii::$app->request;
        $id = $req->post('id');
        $allowable_extetions = [
            'jpg',
            'png',
            // 'svg'
        ];
        $extention = array_pop(explode('.', $_FILES['img']['name']));
        if (!in_array($extention, $allowable_extetions)) {
            return Json::encode(false);
        }
        $img_name = Yii::$app->security->generateRandomString(16) . '.' . $extention;
        $img_temp_name = $_FILES['img']['tmp_name'];
        $root = Yii::getAlias('@webroot');
        $path = '/img/specialist/' . $id . '/';
        FileHelper::createDirectory($root . $path);
        rename($img_temp_name, $root . $path . $img_name);
        $image = Image::getImagine()->open($root . $path . $img_name);
        $m_data = $image->metadata();
        $w = $m_data['computed.Width'];
        $h = $m_data['computed.Height'];
        $s = ($w > $h) ? $h : $w;
        if ($s > 800) {
            $image->thumbnail(new Box(800, 800))->save($root . $path . $img_name, ['quality' => 50]);
        }
        $specialist = Specialist::findOne($id);
        if ($specialist) {
            $specialist->img = '/web' . $path . $img_name;
            if ($specialist->save()) {
                return Json::encode(true);
            }
        }
        return Json::encode(false);

        // return Json::encode([
        //     'id' => $id,
        //     'img_temp' => $img_temp_name,
        //     'img' => $img_name,
        //     'path' => $path,
        //     'size' => $s,
        // ]);
    }
}
