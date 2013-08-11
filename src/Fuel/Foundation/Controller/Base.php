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

use Fuel\Foundation\Response\Base as Response;
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
	 * @var  Fuel\Routing\Match  the route that led us to this controller
	 *
	 * @since  1.0.0
	 */
	protected $route;

	/**
	 * @var  Response
	 *
	 * @since  1.0.0
	 */
	protected $response;

	/**
	 * @var  string  The format of the response this controller returns
	 *
	 * @since  1.0.0
	 */
	protected $responseFormat = 'html';

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
		// get the route that got us here
		$this->route = array_shift($args);

		// And create a response object
		if (empty($this->responseFormat))
		{
			$this->responseFormat = 'html';
		}
		$this->initialResponseFormat = $this->responseFormat;

		$this->response = \Dependency::resolve('response.'.$this->responseFormat, array(\Application::getInstance()));

		// Determine the method to call
		$action = $this->route->action ?: $this->defaultAction;
		if ( ! is_callable(array($this, $method = strtolower($this->route->method).$action)))
		{
			if ( ! is_callable(array($this, $method = $this->actionPrefix.$action)))
			{
				throw new NotFound('No such action "'.$action.'" found in Controller: \\'.get_class($this));
			}
		}

		 // only public methods can be called via a request
		$method = new \ReflectionMethod($this, $method);
		if ( ! $method->isPublic())
		{
			throw new NotFound('Unavailable action "'.$method.'" in Controller: \\'.get_class($this));
		}

		// execute the request
		return $this->execute($method, $args);
	}

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
	protected function execute($method, array $args = array())
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
		// make sure we have a valid response object
		if ( ! $response instanceof Response)
		{
			$this->response->setContent($response);
		}
		elseif ($response !== null)
		{
			$this->response = $response;
		}

		// do we need to repackage the response?
		if ($this->responseFormat !== $this->initialResponseFormat)
		{
			$response = \Dependency::resolve('response.'.$this->responseFormat, array(\Application::getInstance()));
			$this->response = $response->setContent($this->response->getContent());
		}
	}
}
