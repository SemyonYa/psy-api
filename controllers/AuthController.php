<?php

namespace app\controllers;

use app\models\AesToken;
use app\models\User;
use Yii;
use yii\helpers\Json;
use yii\web\Controller;

class AuthController extends Controller
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

    public function actionLogin()
    {
        $req = Yii::$app->request;
        $login = $req->post('login');
        $password = $req->post('password');
        $role = $req->post('app');
        $role_r = explode(',', $role);
        $user = User::findOne(['login' => $login]);
        if ($user) {
            if (in_array($user->role, $role_r)) {
                $user->access_token = AesToken::generate($user);
                if (Yii::$app->security->validatePassword($password, $user->password_hash)) {
                    if ($user->save()) {
                        return Json::encode([
                            'id' => $user->id,
                            'login' => $user->login,
                            'organization' => $user->organization_name,
                            'token' => $user->access_token,
                            'role' => $user->role,
                        ]
                        );
                    }
                }
            }
        }
        return Json::encode(false);
    }
}

// public function actionRefreshTokens() {
//     // $signer = new Sha256();
//     // $token = Yii::$app->request->post('r');
//     // $refresh_token = (new Parser())->parse((string) $token);
//     // $refresh_token->verify($signer, 'psy');
//     // return Json::encode($refresh_token->getHeaders());
//     return Json::encode([
//         'a' => 'NEW_ACCESS_TOKEN',
//         'r' => 'NEW_REFRESH_TOKEN'
//     ]);
// }

// public function generateAccessToken(User $user)
// {
//     $time = new DateTimeImmutable(); //time();
//     $jwt = (new Builder(new LcobucciParser()))
//         ->issuedBy($_SERVER['HTTP_HOST'])
//         ->issuedAt($time)
//         ->expiresAt($time->add(new DateInterval('PT3M')))
//         ->withClaim('uid', $user->id)
//         ->withClaim('urole', $user->role)
//         ->getToken(new Sha256(), new Key('psy'));
//     return $jwt->__toString();
// }

// public function generateRefreshToken(User $user)
// {
//     $time = new DateTimeImmutable(); //time();
//     $jwt = (new Builder(new LcobucciParser()))
//         ->issuedBy($_SERVER['HTTP_HOST'])
//         ->issuedAt($time->add(new DateInterval('P1D')))
//         ->expiresAt($time)
//         ->withClaim('uid', $user->id)
//         ->withClaim('urole', $user->role)
//         ->getToken(new Sha256(), new Key('psy'));
//     return $jwt->__toString();
// }
