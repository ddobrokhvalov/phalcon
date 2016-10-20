<?php
/**
 * Created by PhpStorm.
 * User: knubisoft
 * Date: 20.10.2016
 * Time: 17:28
 */

namespace Multiple\Library;


class Password
{
    private function random_password($chars = 9) {
        $letters = 'abcefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        return substr(str_shuffle($letters), 0, $chars);
    }
}