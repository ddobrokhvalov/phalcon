<?php
namespace Multiple\Library;
class TrustedLibrary{
    public static function load_trusted_common(){
        require(__DIR__."/TrustedLibrary/login/common.php");
        define("TRUSTED_LOGIN_PLUGIN_PATH", "https://net.trusted.ru/static/");
    }

    public static function load_trusted_auth(){
        require(__DIR__."/TrustedLibrary/login/authorize.php");
        var_dump($user);
    }
}