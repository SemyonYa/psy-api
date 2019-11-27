<?php

namespace app\services;

class SmsSender implements ISender
{
    public static function sendMsg($to, $msg)
    {
        return true;
    }
}
