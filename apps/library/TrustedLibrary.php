<?php
namespace Multiple\Library;
class TrustedLibrary{

    static function trusted_library_init(){
        require(__DIR__."/TrustedLibrary/trusted/login/common.php");
        define("TRUSTED_LOGIN_PLUGIN_PATH", "https://net.trusted.ru/static/");
    }

    static function trusted_library_authorize(){
        require(__DIR__."/TrustedLibrary/trusted/settings.php");
        global $DB;
        require(__DIR__."/TrustedLibrary/trusted/login/authorize.php");
        exit();
    }

}