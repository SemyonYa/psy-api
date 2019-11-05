<?php

namespace app\models;

use app\models\User;
use DateTimeImmutable;
use yii\helpers\Json;

class AesToken {
    static private $method = 'aes128';
    static private $key = 'nluxnfizsdfozefoawejfioxdfcml;gnxcuip;mzsefeofz;sf;ixd[pfgkok';
    static private $options = 0;
    static private $iv = '-PsyClientsToken';

    public static function generate(User $user) {
        $data = [
            'id' => $user->id,
            'role' => $user->role,
            'org' => $user->organization_name,
            'issued' => new DateTimeImmutable()
        ];
        return openssl_encrypt(Json::encode($data), self::$method, self::$key, self::$options, self::$iv);
    }

    public static function decode(string $token) {
        return Json::decode(openssl_decrypt($token, self::$method, self::$key, self::$options, self::$iv));
    }
}