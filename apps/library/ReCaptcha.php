<?php
namespace Multiple\Library;


class ReCaptcha
{
    public static function chechCaptcha($data){
        $ip = $_SERVER['REMOTE_ADDR'];
        $secret = '6LdzsQkUAAAAAPTeUGETjJC0Fuojx7-6qa0JkbPo';
        $captcha = $data['g-recaptcha-response'];
        $response=file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$secret."&response=".$captcha."&remoteip=".$ip);
        return json_decode($response,true);
    }
}