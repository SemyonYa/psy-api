<?php

namespace app\controllers;

use app\models\AesToken;
use app\models\User;
use Yii;
use yii\helpers\Json;
use yii\web\UnauthorizedHttpException;

class UserController extends AdminController
{
    public function actionAll()
    {
        // $token = Yii::$app->request->headers->get('PsyAuth');
        // $user_data = AesToken::decode($token);
        // $user = User::findOne($user_data['id']);
        // if ($user) {
        //     throw new UnauthorizedHttpException();
        // }
        // return Json::encode(
        //     [
        //         'token' => $token,
        //         'user-token' => $user->access_token
        //     ]
        // );
        return Json::encode(
            User::find()
                ->where(['blocked' => 0])
                ->select(['id', 'organization_name', 'email', 'login', 'phone', 'specialists_quantity', 'parent_user_id', 'blocked'])
                ->asArray()
                ->all()
        );
    }

    public function actionAllBlocked()
    {
        return Json::encode(
            User::find()
                ->where(['blocked' => 1])
                ->select(['id', 'organization_name', 'email', 'login', 'specialists_quantity', 'parent_user_id', 'blocked'])
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
        $user->organization_name = $req->post('organizationName');
        $user->email = $req->post('email');
        $user->phone = $req->post('phone');
        $user->login = $req->post('login');
        $user->role = 'manager';
        $password = $req->post('password');
        $user->password_hash = Yii::$app->security->generatePasswordHash($password);
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
        $user->organization_name = $req->post('organizationName');
        $user->specialists_quantity = $req->post('specialistsQuantity');
        $user->email = $req->post('email');
        $user->parent_user_id = $req->post('parentUserId');
        $user->login = $req->post('login');
        $user->phone = $req->post('phone');
        $user->role = $req->post('role');
        $user->blocked = ($req->post('blocked')) ? 1 : 0;
        if ($user->save()) {
            return Json::encode($user->id);
        }
        return Json::encode(false);
    }

    public function actionUnlock()
    {
        $id = Yii::$app->request->post('id');
        $user = User::findOne($id);
        $user->blocked = 0;
        if ($user->save()) {
            return $user->id;
        }
        return false;
    }

    public function actionResetPassword() {
        $req = Yii::$app->request;
        $user_id = $req->post('userId');
        $user = User::findOne($user_id);
        $password = $req->post('password');
        $user->password_hash = Yii::$app->security->generatePasswordHash($password);
        if ($user->save()) {
            return Json::encode(true);
        }
        return Json::encode(false);
    }

    public function actionChildren($id)
    {
        return Json::encode(
            User::find()
                ->where(['parent_user_id' => $id])
                ->asArray()
                ->all()
        );
    }

    // public function actionDelete($id)
    // {
    //     $user = User::findOne($id);
    //     $user->blocked = 1;
    //     if ($user->save()) {
    //         return true;
    //     }
    //     return false;
    // }

}
