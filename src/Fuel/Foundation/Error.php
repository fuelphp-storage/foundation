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
	}
}
