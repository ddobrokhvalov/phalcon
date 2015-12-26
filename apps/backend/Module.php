<?php

namespace Multiple\Backend;
use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Dispatcher;
use Phalcon\DiInterface;
use Phalcon\Db\Adapter\Pdo\Mysql as Database;
use Phalcon\Config\Adapter\Ini as ConfigIni;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Events\Manager as EventsManager;

require('../apps/plugins/SecurityPlugin.php');
require('../apps/plugins/NotFoundPlugin.php');
use Multiple\Plugin\SecurityPlugin as SecurityPlugin;
use Multiple\Plugin\NotFoundPlugin as NotFoundPlugin;
class Module
{

	public function registerAutoloaders()
	{

		$loader = new Loader();

		$loader->registerNamespaces(array(
			'Multiple\Backend\Controllers' => '../apps/backend/controllers/',
			'Multiple\Backend\Models'      => '../apps/backend/models/',
			'Multiple\Backend\Plugins'     => '../apps/backend/plugins/',
			'Multiple\Library'             => '../apps/library/',
			'Multiple\Form'                => '../apps/backend/form/',
		));


		$loader->register();
	}

	/**
	 * Register the services here to make them general or register in the ModuleDefinition to make them module-specific
	 */
	public function registerServices(DiInterface $di)
	{

		//Registering a dispatcher
		$di->set('dispatcher', function() {
			// �������� ����������� �������� ������� � ������� DI
			$eventsManager = new EventsManager();

			// ������ ������������ ������� �������, �������������� �����������
			$eventsManager->attach('dispatch:beforeDispatch', new SecurityPlugin);

			// ����������� ���������� � not-found ����������, ��������� NotFoundPlugin
			//$eventsManager->attach('dispatch:beforeException', new NotFoundPlugin);

			$dispatcher = new Dispatcher();

			// ��������� �������� ������� � �����������
			$dispatcher->setEventsManager($eventsManager);

			$dispatcher->setDefaultNamespace("Multiple\Backend\Controllers\\");
			return $dispatcher;
		});

		//Registering the view component
		$di->set('view', function() {
			$view = new View();
			$view->setViewsDir('../apps/backend/views/');
			$view->registerEngines(
				array(
					".phtml" => 'Phalcon\Mvc\View\Engine\Volt'
				)
			);
			return $view;
		});

		//Set a different connection in each module
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
