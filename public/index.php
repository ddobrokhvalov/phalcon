<?php

error_reporting(E_ALL);

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

			/*$router->add('/:controller/:action', array(
				'module' => 'frontend',
				'controller' => 1,
				'action' => 2,
			));*/
			/*$router->setDefaults(array(
				'module' => 'frontend',
				'controller' => 'index',
				'action' => 'index',
			)); */
			$router->add("/admin", array(
				'module' => 'backend',
				'controller' => 'login',
				'action' => 'index',
			));
			$router->add("/esign/ajax.php", array(
				'module' => 'frontend',
				'controller' => 'ajax',
				'action' => 'trusted',
			));
			
			$router->add("/ajax/getlast", array(
				'module' => 'frontend',
				'controller' => 'ajax',
				'action' => 'getlast',
			));

			$router->add("/admin/:controller/:action/:params", array(
				'module' => 'backend',
				'controller' => 1,
				'action' => 2,
				'params'=>3
			));
			$router->add("/:controller/:action/:params", array(
				'module' => 'frontend',
				'controller' => 1,
				'action' => 2,
				'params'=>3
			));
		/*	$router->add("/admin/products/:action", array(
				'module' => 'backend',
				'controller' => 'products',
				'action' => 1,
			));

			$router->add("/products/:action", array(
				'module' => 'frontend',
				'controller' => 'products',
				'action' => 1,
			)); */

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
		/*include "phpMailer/PHPMailerAutoload.php";

		$mail = new PHPMailer();

//$mail->SMTPDebug = 3;                               // Enable verbose debug output

		//$mail->isSMTP();                                      // Set mailer to use SMTP
		//$mail->Host = 'smtp1.example.com;smtp2.example.com';  // Specify main and backup SMTP servers
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = 'vadim-job-hg@yandex.ru';                 // SMTP username
		$mail->Password = 'batosan86()!';                           // SMTP password
                                // TCP port to connect to

		$mail->setFrom('vadim-job-hg@yandex.ru', 'Mailer');
		$mail->addAddress('vadim-job-hg@yandex.ru', 'vadim-job-hg@yandex.ru');     // Add a recipient
		$mail->isHTML(true);                                  // Set email format to HTML

		$mail->Subject = 'Here is the subject';
		$mail->Body    = 'This is the HTML message body <b>in bold!</b>';
		$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

		if(!$mail->send()) {
			echo 'Message could not be sent.';
			echo 'Mailer Error: ' . $mail->ErrorInfo;
		} else {
			echo 'Message has been sent';
		}

		die('');*/
		echo $this->handle()->getContent();
	}

}

$application = new Application();
$application->main();
