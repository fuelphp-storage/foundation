<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation\Controller;

//use View;
//use Response;
use Fuel\Foundation\Exception\NotFound;

/**
 * Controller Base class
 *
 * Default controller class that takes action based on the input it gets.
 *
 * @package  FuelPHP\Foundation
 *
 * @since  1.0.0
 */
abstract class Base
{
	/**
	 * @var  string  default method to call on empty action input
	 *
	 * @since  2.0.0
	 */
	protected $defaultAction = 'Index';

	/**
	 * @var  string  required prefix for method to be accessible as action
	 *
	 * @since  2.0.0
	 */
	protected $actionPrefix = 'action';

	/**
	 * @var  Application  app that created this request
	 *
	 * @since  2.0.0
	 */
	protected $app;

	/**
	 * @var  Fuel\Display\ViewManager  this apps ViewManager
	 *
	 * @since  2.0.0
	 */
	protected $viewManager;

	/**
	 * @var  Request
	 *
	 * @since  1.0.0
	 */
	protected $request;

	/**
	 * @var  Response
	 *
	 * @since  1.0.0
	 */
	protected $response;

	/**
	 * Executes the given method and returns a Response object
	 *
	 * @param   \ReflectionMethod|string  $method
	 * @param   array  $args
	 *
	 * @return  \Response
	 *
	 * @since  2.0.0
	 */
	public function execute($method, array $args = array())
	{
		! $method instanceof \ReflectionMethod and $method = new \ReflectionMethod($this, $method);

		$response = $this->before();
		if ($response instanceof Response)
		{
			$this->response = $response;
		}
		else
		{
			$response = $method->invokeArgs($this, $args);
			$this->after($response);
		}

		return $this->response;
	}

	/**
	 * Method to execute for controller setup
	 *
	 * @return  Response|null
	 *
	 * @since  1.0.0
	 */
	protected function before() {}

	/**
	 * Method to execute for finishing up controller execution, ensures the response is a Response object
	 *
	 * @param   mixed  $response
	 *
	 * @return  Response
	 *
	 * @since  1.0.0
	 */
	protected function after($response)
	{
		if ( ! $response instanceof Response)
		{
			$this->response->setContent($response);
		}
	}

	/**
	 * Makes the Controller instance executable, must be given the URI segments to continue
	 *
	 * @param    array  $args
	 *
	 * @throws  Request\Exception\NotFound
	 *
	 * @return  Response
	 *
	 * @since  2.0.0
	 */
	public function __invoke(array $args)
	{
		// Assign the most relevant objects to the Controller
		$this->app = array_shift($args);
		$this->viewManager = $this->app->getViewManager();

		// And create the Request/Response objects
		$this->request = $this->app->getActiveRequest();
		$this->response = \Dependency::resolve('response', array($this->app));

		// Determine the method
// CHECKME - do we need to camelcase the action?
		$method = $this->actionPrefix.(array_shift($args) ?: $this->defaultAction);

		// Return false if it doesn't exist
		if ( ! method_exists($this, $method))
		{
			throw new NotFound('No such action "'.$method.'" in Controller: \\'.get_class($this));
		}

		/**
		 * Return false if the method isn't public
		 */
		$method = new \ReflectionMethod($this, $method);
		if ( ! $method->isPublic())
		{
			throw new NotFound('Unavailable action "'.$method.'" in Controller: \\'.get_class($this));
		}

		return $this->execute($method, $args);
	}
}
