<?php

namespace app\controllers;

use app\models\Specialist;
use app\models\User;
use Yii;
use yii\helpers\Json;

class ManagerUserController extends ManagerController
{
    public function actionAll()
    {
        $parent_id = Yii::$app->request->get('userId');
        $q = 'SELECT u.id, u.email, u.phone, u.login, s.id AS specialist_id, s.name FROM user as u LEFT JOIN specialist as s ON u.specialist_id = s.id WHERE u.parent_user_id = :id';
        $users = Yii::$app->db->createCommand($q)->bindParam(':id', $parent_id)->queryAll(); 
        return Json::encode($users);
    }

    public function actionOne($id)
    {
        $q = 'SELECT u.id, u.email, u.phone, u.login, s.id AS specialist_id, s.name FROM user as u LEFT JOIN specialist as s ON u.specialist_id = s.id WHERE u.id = :id';
        $user = Yii::$app->db->createCommand($q)->bindParam(':id', $id)->queryOne(); 
        return Json::encode($user);

    }

    public function actionCreate()
    {
        $req = Yii::$app->request;
        $parent_user_id = $req->post('userId');
        $parent_user = User::findOne($parent_user_id);
        
        $user = new User();
        $user->organization_name = $parent_user->organization_name;
        $user->specialists_quantity = 0;
        $user->email = $req->post('email');
        $user->phone = $req->post('phone');
        $user->parent_user_id = $parent_user->id;
        $user->login = $req->post('login');
        $user->password_hash = Yii::$app->security->generatePasswordHash($req->post('password'));
        $user->role = 'spec';
        $user->specialist_id = $req->post('specialistId');

        if ($user->save()) {
            return Json::encode($user->id);
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
        // $parent_user_id = $req->post('userId');
        // $parent_user = User::findOne($parent_user_id);
        
        if ($user->save()) {
            return Json::encode($user->id);
        }
        return Json::encode($user);
        return Json::encode(false);
    }

    public function actionDelete() {
        $req = Yii::$app->request;
        $id = $req->post('id');
        
        return Json::encode(User::findOne($id)->delete());
    }

    public function actionValidateCreateUserLogin() {
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
