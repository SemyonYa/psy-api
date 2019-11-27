<?php

namespace app\services;

class MailSender implements ISender
{
    public static function sendMsg($to, $msg)
    {
        return true;
    }
}
