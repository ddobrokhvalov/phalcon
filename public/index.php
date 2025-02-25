<?php

error_reporting(E_ERROR);

use Phalcon\Loader;
use Phalcon\Mvc\Router;
use Phalcon\DI\FactoryDefault;
use Phalcon\Mvc\Application as BaseApplication;


class Application extends BaseApplication
{

	/**
	 * Register the services here to make them general or register in the ModuleDefinition to make them module-specific
	 */
	protected function registerServices()
	{

		$di = new FactoryDefault();

		$loader = new Loader();

		/**
		 * We're a registering a set of directories taken from the configuration file
		 */
		$loader->registerDirs(
			array(
				__DIR__ . '/../apps/library/',
				__DIR__ . '/../apps/plugins/'
			)
		)->register();



		//Registering a router
		$di->set('router', function(){

			$router = new Router();

			$router->setDefaultModule("frontend");
			$router->setDefaultController("index");
			$router->setDefaultAction("index");

            $router->add("/register/recoveryPassword", array(
                'module' => 'frontend',
                'controller' => 'register',
                'action' => 'recoveryPassword',
            ));
            $router->add("/register/index", array(
                'module' => 'frontend',
                'controller' => 'register',
                'action' => 'index',
            ));

            $router->add("/help/about", array(
                'module' => 'frontend',
                'controller' => 'help',
                'action' => 'about',
            ));

			
			$router->add("/ajax/getlast", array(
				'module' => 'frontend',
				'controller' => 'ajax',
				'action' => 'getlast',
			));
			
			$router->add("/:controller/:action/:params", array(
				'module' => 'frontend',
				'controller' => 1,
				'action' => 2,
				'params'=>3
			));

			$router->add("/admin/:controller/:action/:params", array(
				'module' => 'backend',
				'controller' => 1,
				'action' => 2,
				'params'=>3
			));

//            $router->add("/admin/register", array(
//                'module' => 'backend',
//                'controller' => 'register',
//                'action' => 'index',
//            ));

			$router->add("/admin", array(
				'module' => 'backend',
				'controller' => 'login',
				'action' => 'index',
			));

			$router->add("/admin/", array(
				'module' => 'backend',
				'controller' => 'login',
				'action' => 'index',
			));

			$router->add("/admin/login", array(
				'module' => 'backend',
				'controller' => 'login',
				'action' => 'index',
			));



//			$router->notFound(array(
//				"controller" => "index",
//				"action" => "page404",
//			));

			return $router;

		});

		$this->setDI($di);
	}

	public function main()
	{

		$this->registerServices();

		//Register the installed modules
		$this->registerModules(array(
			'frontend' => array(
				'className' => 'Multiple\Frontend\Module',
				'path' => '../apps/frontend/Module.php'
			),
			'backend' => array(
				'className' => 'Multiple\Backend\Module',
				'path' => '../apps/backend/Module.php'
			)
		));
		echo $this->handle()->getContent();
	}

}

$application = new Application();
$application->main();
