<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation\Facades;

/**
 * Error Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Error extends Base
{
	/**
	 * @var  Whoops\Run
	 *
	 * @since  2.0.0
	 */
	protected static $errorHandler;

	/**
	 * Initialization, set the Error handler
	 *
	 * @since  2.0.0
	 */
	public static function initialize($handler = null)
	{
		if ($handler === null)
		{
			// use the framework default Whoops error handler
			static::$errorHandler = new \Whoops\Run;

			// define the page handler TODO (deal with AJAX/JSON)
			$pagehandler = new \Whoops\Handler\PrettyPageHandler;
			$pagehandler->setResourcesPath(__DIR__.DS.'..'.DS.'Exception'.DS.'resources');

			$pagehandler->addDataTableCallback('Current Request', function()
			{
				$application = \Application::getInstance();
				$environment = \Environment::getInstance();
				$request     = \Request::getActive();
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
				$application = \Application::getInstance();
				return $application ? $application->getSession()->getContents() : '';
			});
			$pagehandler->addDataTableCallback('Flash Session Data', function()
			{
				$application = \Application::getInstance();
				return $application ? $application->getSession()->getContentsFlash() : '';
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

			static::$errorHandler->pushHandler($pagehandler);

			static::$errorHandler->register();
		}
		else
		{
			// set a custom handler
			static::$errorHandler = $handler;
		}
	}

	/**
	 * Get the object instance for this Facade
	 *
	 * @returns Whoops\Run
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		return static::$errorHandler;
	}
}
