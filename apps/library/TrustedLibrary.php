<?php
namespace Multiple\Library;
use Phalcon\Di;
class TrustedLibrary{

    static function get_settings(){


        $di = DI::getDefault();
        $config =$di->get("config");
        define("TRUSTED_DB", true);
        define("TRUSTED_DB_HOST", $config['host']);
        define("TRUSTED_DB_NAME", $config['dbname']);
        define("TRUSTED_DB_LOGIN", $config['username']);
        define("TRUSTED_DB_PASSWORD", $config['password']);
    }

    static function trusted_library_init(){
        self::get_settings();
        require(__DIR__."/TrustedLibrary/trusted/login/common.php");
        define("TRUSTED_LOGIN_PLUGIN_PATH", "https://net.trusted.ru/static/");
    }

    static function trusted_library_authorize(){
        self::get_settings();
        require(__DIR__."/TrustedLibrary/trusted/settings.php");
        global $DB;
        require(__DIR__."/TrustedLibrary/trusted/login/authorize.php");
        exit();
    }

}