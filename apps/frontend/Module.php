<?php

namespace Multiple\Frontend;

use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Dispatcher;
use Phalcon\DiInterface;
use Phalcon\Db\Adapter\Pdo\Mysql as Database;
use Phalcon\Config\Adapter\Ini as ConfigIni;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Events\Manager as EventsManager;

use Multiple\Frontend\Plugins\SecurityPlugin;
use Multiple\Frontend\Plugins\NotFoundPlugin;

class Module
{

	public function registerAutoloaders()
	{

		$loader = new Loader();

		$loader->registerNamespaces(array(
			'Multiple\Frontend\Controllers' => '../apps/frontend/controllers/',
			'Multiple\Frontend\Models' => '../apps/frontend/models/',
            'Multiple\Backend\Models'      => '../apps/backend/models/',
			'Multiple\Frontend\Plugins'     => '../apps/frontend/plugins/',
			'Multiple\Frontend\Form'        => '../apps/frontend/form/',
			'Multiple\Library'     => '../apps/library/',

		));

		$loader->register();
	}

	/**
	 * Register the services here to make them general or register in the ModuleDefinition to make them module-specific
	 */
	public function registerServices($di)
	{

		//Registering a dispatcher
		$di->set('dispatcher', function () {
			// Получаем стандартный менеджер событий с помощью DI
			$eventsManager = new EventsManager();

			// Плагин безопасности слушает события, инициированные диспетчером
			$eventsManager->attach('dispatch:beforeDispatch', new SecurityPlugin);

			// Отлавливаем исключения и not-found исключения, используя NotFoundPlugin
			//	$eventsManager->attach('dispatch:beforeException', new NotFoundPlugin);

			$dispatcher = new Dispatcher();

			// Связываем менеджер событий с диспетчером
			$dispatcher->setEventsManager($eventsManager);
			$dispatcher->setDefaultNamespace("Multiple\Frontend\Controllers\\");
			return $dispatcher;
		});

		//Registering the view component
		$di->set('view', function() {
			$view = new View();
			$view->setViewsDir('../apps/frontend/views/');
			$view->registerEngines(
				array(
					".phtml" => //'Phalcon\Mvc\View\Engine\Volt'
                    function($view, $di){
                        $volt = new \Phalcon\Mvc\View\Engine\Volt($view, $di);
                        $volt->setOptions(array(
                            'compileAlways' => true
                            //"true" if you need to renew children templates after base template changed
                            //or remove or compiled files or make changes in children
                        ));
                        $compiler = $volt->getCompiler();
                        $compiler->addFunction('nl2br', function ($str) {return "nl2br($str)";});
                        $compiler->addFilter('strtotime', 'strtotime');
                        $compiler->addFilter('count', 'count');
                        $compiler->addFilter('$(this).text()', 'length');
                        return $volt;
                    }
				)
			);
			return $view;
		});
         $di->set('config',function(){
			 $config = new ConfigIni("config/config.ini");
			 return $config->database->toArray();
		 });
		$di->set('db', function() {
			$config = new ConfigIni("config/config.ini");
			return new Database($config->database->toArray());
		});
		$di->set('session', function () {
			$session = new SessionAdapter();
			$session->start();
			return $session;
		});
	}
}
