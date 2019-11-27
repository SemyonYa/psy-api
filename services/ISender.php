<?php

namespace app\services;

interface ISender
{
    public static function sendMsg($to, $msg);
}
