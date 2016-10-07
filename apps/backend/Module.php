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
use Phalcon\Flash\Direct as FlashDirect;

use Multiple\Backend\Plugins\SecurityPlugin;
use Multiple\Backend\Plugins\NotFoundPlugin;
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
			'Multiple\Library\PHPImageWorkshop' => '../apps/library/PHPImageWorkshop/',
			'Multiple\Backend\Form'        => '../apps/backend/form/',
			'Multiple\Backend\Validator'        => '../apps/backend/validator/',
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
			// Получаем стандартный менеджер событий с помощью DI
			$eventsManager = new EventsManager();

			// Плагин безопасности слушает события, инициированные диспетчером
			$eventsManager->attach('dispatch:beforeDispatch', new SecurityPlugin);
			$eventsManager->attach('dispatch:beforeException', new NotFoundPlugin);

			// Отлавливаем исключения и not-found исключения, используя NotFoundPlugin
		//	$eventsManager->attach('dispatch:beforeException', new NotFoundPlugin);

			$dispatcher = new Dispatcher();

			// Связываем менеджер событий с диспетчером
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
					".phtml" => //'Phalcon\Mvc\View\Engine\Volt'
					function($view, $di){
						$volt = new \Phalcon\Mvc\View\Engine\Volt($view, $di);
						$volt->setOptions(array(
							'compileAlways' => true
							//"true" if you need to renew children templates after base template changed
							//or remove or compiled files or make changes in children
						));
						$compiler = $volt->getCompiler();
                        $compiler->addFunction('truncate', function ($str) {return "mb_substr ($str) . '...'";});
                        $compiler->addFunction('delete_slash', function ($str) {return "str_replace('\r\n', ' ', $str)";});
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

		$di->set('flash', function () {
			return new FlashDirect();
		});

        $di->set('mailer', function() {
            $service = new MailerService();
//            $service = new MailerService([
//                'driver' => 'smtp', // mail, sendmail, smtp
//                'host'   => 'smtp.email.com',
//                'port'   => 587,
//                'from'   => [
//                    'address' => 'no-reply@my-domain.com',
//                    'name'    => 'My Cool Company',
//                ],
//                'encryption' => 'tls',
//                'username'   => 'no-reply@my-domain.com',
//                'password'   => 'some-strong-password',
//                'sendmail'   => '/usr/sbin/sendmail -bs',
//                // Путь используемый для поиска шаблонов писем
//                'viewsDir'   => __DIR__ . '/../app/views/', // optional
//            ]);


//            $service = new MailerService([
//                'driver' => 'mail', // mail, sendmail, smtp
//                'host'   => 'smtp.email.com',
//                'port'   => 587,
//                'from'   => [
//                    'address' => 'no-reply@my-domain.com',
//                    'name'    => 'My Cool Company',
//                ],
//                'encryption' => 'tls',
//                'username'   => 'no-reply@my-domain.com',
//                'password'   => 'some-strong-password',
//                'sendmail'   => '/usr/sbin/sendmail -bs',
//                // Путь используемый для поиска шаблонов писем
//                'viewsDir'   => __DIR__ . '/../app/views/', // optional
//            ]);
            return $service->mailer();
        });

	}
}
