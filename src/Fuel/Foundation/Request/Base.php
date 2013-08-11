<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation\Request;

/**
 * FuelPHP Request base class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
abstract class Base
{
	/**
	 * @var  Application  app that created this request
	 *
	 * @since  2.0.0
	 */
	protected $app;

	/**
	 * @var  string
	 *
	 * @since  2.0.0
	 */
	protected $request = '';

	/**
	 * @var  \Fuel\Kernel\Request\Input\Base
	 *
	 * @since  2.0.0
	 */
	protected $input;

	/**
	 * @var  array  associative array of named params in the URI
	 *
	 * @since  1.0.0
	 */
	protected $params = array();

	/**
	 * @var  Response  Response after execution
	 *
	 * @since  1.0.0
	 */
	protected $response;

	/**
	 * @var  Route  Current route
	 *
	 * @since  1.0.0
	 */
	protected $route;

	/**
	 * Constructor
	 *
	 * @param  string  $resource
	 * @param  array|Input  $input
	 *
	 * @since  1.0.0
	 */
	public function __construct($app, $resource = '', $input = null)
	{
		$this->app = $app;

		// get the parents input, or the global instance of no parent is active
		$inputInstance = ($request = \Request::getInstance()) ? $request->getInput() : \Input::getInstance();

		// and create a new local input instance
		$input = is_array($input) ? $input : array();

		$this->input = \Input::forge(\Application::getInstance(), $input, $inputInstance);

		// store the request
		$this->request = $resource;
	}

	/**
	 * Execute the request
	 *
	 * @return  Request
	 * @throws  \Exception
	 * @throws  \DomainException
	 *
	 * @since  1.0.0
	 */
	abstract public function execute();

	/**
	 * Returns this requests Application instance
	 *
	 * @return  Application
	 *
	 * @since  1.1.0
	 */
	public function getApplication()
	{
		return $this->app;
	}

	/**
	 * Fetch a named parameter from the request
	 *
	 * @param   null|string  $param
	 * @param   mixed        $default
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function getParam($param = null, $default = null)
	{
		if (is_null($param))
		{
			return $this->params;
		}

		return isset($this->params[$param]) ? $this->params[$param] : $default;
	}

	/**
	 * Fetch the request response after execution
	 *
	 * @return  \Fuel\Foundation\Response
	 *
	 * @since  1.0.0
	 */
	public function getResponse()
	{
		return $this->response;
	}

	/**
	 * Returns this requests Input instance
	 *
	 * @return  Input
	 *
	 * @since  1.1.0
	 */
	public function getInput()
	{
		return $this->input;
	}

	/**
	 * Returns this requests current active Route
	 *
	 * @return  Route
	 *
	 * @since  1.1.0
	 */
	public function getRoute()
	{
		return $this->route;
	}

	/**
	 * Allows setting a response object for errors or executing a fallback
	 *
	 * @param   \Fuel\Foundation\Exception\Base  $e
	 * @return  \Fuel\Foundation\Response
	 * @throws  \Fuel\Foundation\Exception\Base
	 */
	protected function errorResponse(Exception\Base $e)
	{
		throw $e;
	}
}
