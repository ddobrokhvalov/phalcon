<?php

use Phalcon\Cli\Console as ConsoleApp;
use Phalcon\Config\Adapter\Ini as ConfigIni;
use Phalcon\Db\Adapter\Pdo\Mysql as Database;
use Phalcon\Di\FactoryDefault\Cli as CliDI;

define('VERSION', '1.0.0');
//\Phalcon\Mvc\Model::setup(array('notNullValidations' => false));
$di = new CliDI();
$loader = new \Phalcon\Loader();
defined('APP_PATH') || define('APP_PATH', realpath(dirname(__FILE__)));
$loader->registerDirs(array(
        APP_PATH . '/../apps/cron',
        APP_PATH . '/../apps/library',
        APP_PATH . '/../apps/library/xpress',
        APP_PATH . '/../apps/library/PHPMailer'
    )
)->registerNamespaces(array(
    'Multiple\Backend\Models' => APP_PATH . '/../apps/backend/models/',
    'Multiple\Frontend\Models' => APP_PATH . '/../apps/frontend/models/',
    'Multiple\Library' => APP_PATH . '/../apps/library/',
))->register();

$di->set('config', function () {
    $config = new ConfigIni(APP_PATH . '/../config/config.ini');//get from backend
    return $config->api->toArray();
});

$di->set('db', function () {
    $config = new ConfigIni(APP_PATH . '/../apps/backend/config/config.ini');
    return new Database($config->database->toArray());
});

$di->set('dbconfig', function () {
    $config = new ConfigIni(APP_PATH . '/../apps/frontend/config/config.ini');//get from backend
    return $config->database;
});
$di->set('logger', function ($logfile) {
    return new \Phalcon\Logger\Adapter\File(realpath('../apps/logs').DIRECTORY_SEPARATOR.$logfile.'.log');
});
$console = new ConsoleApp();
$console->setDI($di);

$arguments = array();
foreach ($argv as $k => $arg) {
    if ($k == 1) {
        $arguments['task'] = $arg;
    } elseif ($k == 2) {
        $arguments['action'] = $arg;
    } elseif ($k >= 3) {
        $arguments['params'][] = $arg;
    }
}

define('CURRENT_TASK', (isset($argv[1]) ? $argv[1] : null));
define('CURRENT_ACTION', (isset($argv[2]) ? $argv[2] : null));

try {
    $console->handle($arguments);
} catch (\Phalcon\Exception $e) {
    echo $e->getMessage();
    exit(255);
}