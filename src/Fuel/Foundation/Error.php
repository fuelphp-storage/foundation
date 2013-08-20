<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation;

use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;

/**
 * Error class, implements the Whoops error handler
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Error
{
	/**
	 * @var  Whoops\Run  the Whoops error handler instance
	 */
	protected $whoops;

	/**
	 * Initialization, set the Error handler
	 *
	 * @since  2.0.0
	 */
	public function __construct()
	{
		// use the framework default Whoops error handler
		$this->whoops = new Run;

		// define the page handler TODO (deal with AJAX/JSON)
		$pagehandler = new PrettyPageHandler;
		$pagehandler->setResourcesPath(__DIR__.DS.'Exception'.DS.'resources');

		$pagehandler->addDataTableCallback('Current Request', function()
		{
			$application = \Application::getInstance();
			$environment = $application->getEnvironment();
			$request     = \Request::getInstance();
			$route = $request ? $request->getRoute() : null;
			$controller = $route ? $route->controller : '';
			$parameters = $route ? $route->parameters : array();
			array_shift($parameters);

			return array(
				'Application'  => $application ? $application->getName() : '',
 				'Environment'  => $environment ? $environment->getName() : '',
				'Original URI' => $route ? $route->uri : '',
				'Mapped URI'   => $route ? $route->translation : '',
				'Namespace'    => $route ? $route->namespace : '',
				'Controller'   => $controller,
				'Action'       => $controller ? ('action'.$route->action) : '',
				'HTTP Method'  => $request ? \Input::getMethod() : '',
				'Parameters'   => $parameters,
			);
		});
		$pagehandler->addDataTableCallback('Request Parameters', function()
		{
			$input = \Input::getInstance();
			return $input ? $input->getParam()->getContents() : '';
		});
		$pagehandler->addDataTableCallback('Permanent Session Data', function()
		{
			if ($application = \Application::getInstance())
			{
				if ($session = $application->getSession())
				{
					return $session->getContents();
				}
			}
			return 'no session active';
		});
		$pagehandler->addDataTableCallback('Flash Session Data', function()
		{
			if ($application = \Application::getInstance())
			{
				if ($session = $application->getSession())
				{
					return $session->getContentsFlash();
				}
			}
			return 'no session active';
		});
		$pagehandler->addDataTableCallback('Defined Cookies', function()
		{
			$input = \Input::getInstance();
			return $input ? $input->getCookie() : '';
		});
		$pagehandler->addDataTableCallback('Uploaded Files', function()
		{
			$input = \Input::getInstance();
			return $input ? $input->getFile() : '';
		});
		$pagehandler->addDataTableCallback('Server Data', function()
		{
			return $_SERVER;
		});

		$this->whoops->pushHandler($pagehandler);

		$this->whoops->register();

		// set a custom handler, so we can deal with translations
		$current_handler = set_exception_handler(function($e) use(&$current_handler)
		{
			// get the locale
			if (($locale = setlocale(LC_MESSAGES, null)) == 'C')
			{
				// default to en_US if LANG=C is detected
				$locale = 'en_US';
			}

			// get access to the exception's error message
			$reflection = new \ReflectionClass($e);
			$property = $reflection->getProperty("message");
			$property->setAccessible(true);

			// load the translator class, and translate if found
			$class = 'Fuel\Translations\\'.$locale;
			if (class_exists($class, true))
			{
				$property->setValue($e, $class::get($e->getMessage()));
			}
			else
			{
				$class = 'Fuel\Translations\\'.ucfirst(substr($locale,0,2));
				if (class_exists($class, true))
				{
					$property->setValue($e, $class::get($e->getMessage()));
				}
			}

			call_user_func($current_handler, $e);
		});
	}
}
