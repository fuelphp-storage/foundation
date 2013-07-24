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
	protected $params;

	/**
	 * @var  Response  Response after execution
	 *
	 * @since  1.0.0
	 */
	protected $response;

	/**
	 * @var  array  active Request stack before activation of this one
	 *
	 * @since  2.0.0
	 */
	protected $activeRequests = array();

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
		$inputInstance = ($request = $this->app->getActiveRequest()) ? $request->getInput() : \Fuel::getInput();

		// and create a new local input instance
		$input = is_array($input) ? $input : array();

		$this->input = \Fuel::resolve('input', array($this->app, $input, $inputInstance));
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
		$this->app->setActiveRequest($this);

		list($this->controller, $this->controllerParams, $this->params) = $this->app->getRouter()->route($this->requestUri);

		// push the current app object so we have it available in the controller
		array_unshift($this->controllerParams, $this->app);

		try
		{
			if ( ! is_callable($this->controller))
			{
				throw new \DomainException('The Controller returned by routing is not callable.');
			}

			try
			{
				$this->response = call_user_func($this->controller, $this->controllerParams);
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

			// Render body before finishing the Request when a Viewable was returned
			if (($body = $this->response->getContent()) instanceof Viewable)
			{
				$this->response->setContent($body->render());
			}
		}
		catch (\Exception $e)
		{
			// reset and rethrow
			$this->app->resetActiveRequest();
			throw $e;
		}

		$this->app->resetActiveRequest();

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
	 * @return  \Fuel\Kernel\Response\Base
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
	 * @return  Base
	 *
	 * @since  1.1.0
	 */
	public function getInput()
	{
		return $this->input;
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
