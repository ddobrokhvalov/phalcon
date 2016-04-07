<?php
namespace Multiple\Library;
use Phalcon\Di;
use Multiple\Frontend\Models\Configurations;
class TrustedLibrary{

    static function get_settings(){


        $di = DI::getDefault();
        $config =$di->get("config");
        define("TRUSTED_DB", true);
        define("TRUSTED_DB_HOST", $config['host']);
        define("TRUSTED_DB_NAME", $config['dbname']);
        define("TRUSTED_DB_LOGIN", $config['username']);
        define("TRUSTED_DB_PASSWORD", $config['password']);
        $configurations_arr = Configurations::find(
            array(
                "group_name = :group_name:",
                'bind' => array(
                    'group_name' => 'trustedlogin',
                )
            )
        );
        if(count($configurations_arr))
            foreach($configurations_arr as $conf_item)
                define($conf_item->name, $conf_item->value);
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

    static function trusted_esign(){
        self::get_settings();
        require_once(__DIR__."/TrustedLibrary/trusted/esign/config.php");
        require_once TRUSTED_MODULE_SIGN;
        header('Content-Type: application/json; charset=' . LANG_CHARSET);
        exit();
    }

}