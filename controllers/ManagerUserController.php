<?php

namespace app\controllers;

use app\models\User;
use Imagine\Image\Box;
use Yii;
use yii\helpers\FileHelper;
use yii\helpers\Json;
use yii\imagine\Image;

class ManagerUserController extends ManagerController
{
    public function actionAll()
    {
        $parent_id = Yii::$app->request->get('userId');
        $q = 'SELECT u.id, u.email, u.phone, u.login, u.img, s.id AS specialist_id, s.name, s.img as specialist_img FROM user as u LEFT JOIN specialist as s ON u.specialist_id = s.id WHERE u.parent_user_id = :id';
        $users = Yii::$app->db->createCommand($q)->bindParam(':id', $parent_id)->queryAll();
        return Json::encode($users);
    }

    public function actionOne($id)
    {
        $q = 'SELECT u.id, u.email, u.phone, u.login, u.img, s.id AS specialist_id, s.name, s.img as specialist_img FROM user as u LEFT JOIN specialist as s ON u.specialist_id = s.id WHERE u.id = :id';
        $user = Yii::$app->db->createCommand($q)->bindParam(':id', $id)->queryOne();
        return Json::encode($user);
    }

    public function actionCreate()
    {
        $req = Yii::$app->request;
        $parent_user_id = $req->post('userId');
        $parent_user = User::findOne($parent_user_id);
        $specialist_id = $req->post('specialistId');

        $users_exists = User::find()->where(['parent_user_id' => $parent_user_id, 'specialist_id' => $specialist_id])->all();

        if (!$users_exists) {
            $user = new User();
            $user->organization_name = $parent_user->organization_name;
            $user->specialists_quantity = 0;
            $user->email = $req->post('email');
            $user->phone = $req->post('phone');
            $user->parent_user_id = $parent_user->id;
            $user->login = $req->post('login');
            $user->password_hash = Yii::$app->security->generatePasswordHash($req->post('password'));
            $user->role = 'spec';
            $user->specialist_id = $specialist_id;

            if ($user->save()) {
                return Json::encode($user->id);
            }
        }
        return Json::encode(false);
    }

    public function actionUpdate()
    {
        $req = Yii::$app->request;
        $id = $req->post('id');
        $user = User::findOne($id);
        $user->login = $req->post('login');
        $user->phone = $req->post('phone');
        $user->email = $req->post('email');

        if ($user->save()) {
            return Json::encode($user->id);
        }
        return Json::encode(false);
    }

    public function actionDelete()
    {
        $req = Yii::$app->request;
        $id = $req->post('id');

        return Json::encode(User::findOne($id)->delete());
    }

    public function actionValidateCreateUserLogin()
    {
        $req = Yii::$app->request;
        $login = $req->post('login');
        $current_user_id = $req->post('currentUserId');

        $user = User::find()->where(['login' => $login])->andWhere(['!=', 'id', $current_user_id])->one();
        if ($user) {
            return Json::encode(['loginIsUsed' => true]);
        } else {
            return Json::encode(null);
        }

    }

    public function actionOpenProfile()
    {
        $profile_id = Yii::$app->request->get('userId');
        return Json::encode(User::find()->where(['id' => $profile_id])->select(['id', 'organization_name', 'email', 'phone', 'specialists_quantity', 'img'])->one());
    }

    public function actionSaveProfile()
    {
        $req = Yii::$app->request;
        $id = $req->post('userId');
        $user = User::findOne($id);
        $user->organization_name = $req->post('organizationName');
        $user->email = $req->post('email');
        $user->phone = $req->post('phone');
        if ($user->save()) {
            return Json::encode(true);
        } else {
            return Json::encode($user);
            return Json::encode(false);
        }
    }

    public function actionResetPassword()
    {
        $req = Yii::$app->request;
        $id = $req->post('userId');
        $new_password = $req->post('newPassword');
        $confirm_password = $req->post('confirmPassword');
        if ($new_password === $confirm_password) {
            $user = User::findOne($id);
            $user->password_hash = Yii::$app->security->generatePasswordHash($new_password);
            if ($user->save()) {
                return Json::encode(true);
            }
        }
        return Json::encode(false);
    }

    public function actionResetChildPassword()
    {
        $req = Yii::$app->request;
        $id = $req->post('userId');
        $new_password = $req->post('newPassword');
        $confirm_password = $req->post('confirmPassword');
        if ($new_password === $confirm_password) {
            $user = User::findOne($id);
            $user->password_hash = Yii::$app->security->generatePasswordHash($new_password);
            if ($user->save()) {
                return Json::encode(true);
            }
        }
        return Json::encode(false);
    }

    public function actionLoadLogo()
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
        $path = '/img/user/' . $id . '/';
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
        $user = User::findOne($id);
        if ($user) {
            $user->img = '/web' . $path . $img_name;
            if ($user->save()) {
                return Json::encode(true);
            }
        }
        return Json::encode(false);

    }

    // public function actionCheckEditName() {
    //     $req = Yii::$app->request;
    //     $user_id = $req->post('userId');
    //     $name = $req->post('name');
    //     $id = $req->post('id');

    //     $specialist = Specialist::find()->where(['user_id' => $user_id, 'name' => $name])->andWhere(['!=', 'id', $id])->one();
    //     if ($specialist) {
    //         return Json::encode(['nameIsUsed' => true]);
    //     } else {
    //         return Json::encode(null);
    //     }

    // }
}
