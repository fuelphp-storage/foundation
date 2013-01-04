<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    FuelPHP\Foundation
 * @version    2.0
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 */

namespace FuelPHP\Foundation\Controller;

use FuelPHP\Foundation\View;
use FuelPHP\Foundation\Response;
use FuelPHP\Foundation\Exception\NotFound;

/**
 * Controller Base class
 *
 * Default controller class that takes action based on the input it gets.
 *
 * @package  Fuel\Kernel
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
	 * @var  \FuelPHP\Foundation\Environment
	 *
	 * @since  2.0.0
	 */
	public $env;

	/**
	 * @var  \FuelPHP\Foundation\Application  app that created this request
	 *
	 * @since  2.0.0
	 */
	public $app;

	/**
	 * @var  \FuelPHP\Foundation\Request
	 *
	 * @since  1.0.0
	 */
	public $request;

	/**
	 * Executes the given method and returns a Response object
	 *
	 * @param   \ReflectionMethod|string  $method
	 * @param   array  $args
	 *
	 * @return  \FuelPHP\Foundation\Response
	 *
	 * @since  2.0.0
	 */
	public function execute($method, array $args = array())
	{
		! $method instanceof \ReflectionMethod and $method = new \ReflectionMethod($this, $method);

		$this->before();
		$response = $method->invokeArgs($this, $args);
		$response = $this->after($response);

		return $response;
	}

	/**
	 * Method to execute for controller setup
	 *
	 * @return void
	 *
	 * @since  1.0.0
	 */
	protected function before() {}

	/**
	 * Method to execute for finishing up controller execution, ensures the response is a Response object
	 *
	 * @param   mixed  $response
	 *
	 * @return  \FuelPHP\Foundation\Response
	 *
	 * @since  1.0.0
	 */
	protected function after($response)
	{
		if ( ! $response instanceof Response)
		{
			$response = $this->env->forge('\FuelPHP\Foundation\Response', null, $response);
		}

		return $response;
	}

	/**
	 * Makes the Controller instance executable, must be given the URI segments to continue
	 *
	 * @param    array  $args
	 *
	 * @throws  \FuelPHP\Foundation\Request\Exception\NotFound
	 *
	 * @return  \FuelPHP\Foundation\Response
	 *
	 * @since  2.0.0
	 */
	public function __invoke(array $args)
	{
		$this->env = \FuelPHP\Foundation\Environment::singleton();
		$this->app = $this->env->getActiveApplication();
		$this->request = $this->app->getActiveRequest();

		// Determine the method
// CHECKME - need to camelcase the action!
		$method = $this->actionPrefix.(array_shift($args) ?: $this->defaultAction);

		// Return false if it doesn't exist
		if ( ! method_exists($this, $method))
		{
			throw new NotFound('No such action "'.$method.'" in Controller: '.get_class($this));
		}

		/**
		 * Return false if the method isn't public
		 */
		$method = new \ReflectionMethod($this, $method);
		if ( ! $method->isPublic())
		{
			throw new NotFound('Unavailable action "'.$method.'" in Controller: '.get_class($this));
		}

		return $this->execute($method, $args);
	}
}
