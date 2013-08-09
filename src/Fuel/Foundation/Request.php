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

use Fuel\Foundation\Exception\NotFound;

/**
 * FuelPHP Request class
 *
 * Initiate a request from the URI passed
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Request
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
	protected $requestUri = '';

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

		$this->requestUri  = '/'.trim(strval($resource), '/');

		// get the parents input, or the global instance of no parent is active
		$inputInstance = ($request = \Request::getInstance()) ? $request->getInput() : \Input::getInstance();

		// and create a new local input instance
		$input = is_array($input) ? $input : array();

		$this->input = \Input::forge($app, $input, $inputInstance);
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
	public function execute()
	{
		\Request::setActive($this);

		// log the request
		\Log::info('Executing request');

		// get a route object for this requestUri
		$this->route = \Router::translate($this->requestUri, \Input::getInstance()->getMethod() );

		// log the request destination
		\Log::info('Request routed to '.$this->route->translation);

		// store the request parameters
		$this->params = array_merge($this->params, $this->route->parameters);

		// push any remaining segments so they'll be available as action arguments
		if ( ! empty($this->route->segments))
		{
			$this->route->parameters = array_merge($this->route->parameters, $this->route->segments);
		}

		// push the action
		array_unshift($this->route->parameters, $this->route->action);

		// push the current app object so we have it available in the controller
		array_unshift($this->route->parameters, $this->app);

		try
		{
			if ( ! is_callable($this->route->controller))
			{
				throw new NotFound('The Controller returned by routing is not callable.');
			}

			// add the root path to the config, lang and view manager objects
			$this->app->getViewManager()->getFinder()->addPath($this->route->path);
			$this->app->getConfig()->addPath($this->route->path);

			try
			{
				$this->response = call_user_func($this->route->controller, $this->route->parameters);
			}
			catch (Exception\Redirect $e)
			{
				$this->response = $e->response($this->app);
			}
			catch (Exception\Base $e)
			{
				$this->response = $this->errorResponse($e);
			}
			catch (\Exception $e)
			{
				// rethrow
				throw $e;
			}

			if ( ! is_object($this->getResponse()) or array_diff(
					get_class_methods('Fuel\Foundation\Response'),
					get_class_methods($this->getResponse())
				) != array())
			{
				throw new \DomainException('Result object from a Controller must'.
					' implement all methods from \Fuel\Foundation\Response.');
			}
		}
		catch (\Exception $e)
		{
			// log the request termination
			\Log::info('Request executed, but failed');

			// reset and rethrow
			\Request::resetActive();
			throw $e;
		}

		// remove the root path to the config, lang and view manager objects
		$this->app->getConfig()->removePath($this->route->path);
		$this->app->getViewManager()->getFinder()->removePath($this->route->path);

		// log the request termination
		\Log::info('Request executed');

		\Request::resetActive();

		return $this;
	}

	/**
	 * Fetch a named parameter from the request URI
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
