<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
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
	 * @var  Component  Component that created this request
	 *
	 * @since  2.0.0
	 */
	protected $component;

	/**
	 * @var  RequestInjectionFactory  this applications object factory
	 *
	 * @since  2.0.0
	 */
	protected $factory;

	/**
	 * @var  string
	 *
	 * @since  2.0.0
	 */
	protected $request = '';

	/**
	 * @var  \Fuel\Foundation\Input
	 *
	 * @since  2.0.0
	 */
	protected $input;

	/**
	 * @var  \Fuel\Config\Container
	 *
	 * @since  2.0.0
	 */
	protected $config;

	/**
	 * @var  \Fuel\Foundation\Uri
	 */
	protected $uri;

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
	public function __construct($component, $resource = '', $input = null, RequestInjectionFactory $factory)
	{
		// store the calling component
		$this->component = $component;

		// store the injecttion factory
		$this->factory = $factory;

		// store the requests input container
		$this->input = $input;

		// store the request
		$this->request = $resource;

		// get the log instance
		$this->log = $component->getApplication()->getLog();

		// get the config instance
		$this->config = $component->getConfig();
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
	 * Returns this requests Component instance
	 *
	 * @return  Component
	 *
	 * @since  1.1.0
	 */
	public function getComponent()
	{
		return $this->component;
	}

	/**
	 * Sets this requests Component instance
	 *
	 * @param  Component  $component
	 *
	 * @since  1.1.0
	 */
	public function setComponent($component)
	{
		$this->component = $component;
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
	 * Returns this requests current Uri object
	 *
	 * @return  Uri
	 *
	 * @since  1.1.0
	 */
	public function getUri()
	{
		return $this->uri;
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
