<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation\Controller;

use Fuel\Foundation\InjectionFactory;
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
	 * @var  Fuel\Foundation\InjectorFactory  injector factory for this object
	 *
	 * @since  2.0.0
	 */
	protected $factory;

	/**
	 * @var  Fuel\Foundation\Application  application this controller belongs to
	 *
	 * @since  2.0.0
	 */
	protected $app;

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
	 * @var  Fuel\Routing\Match  the route that led us to this controller
	 *
	 * @since  1.0.0
	 */
	protected $route;

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
	 * @var  string  The format of the response this controller returns
	 *
	 * @since  1.0.0
	 */
	protected $responseFormat;

	/**
	 *
	 */
	public function __construct(InjectionFactory $factory)
	{
		// store the factory for future use
		$this->factory = $factory;
	}

	/**
	 * Sets this controllers Application instance
	 *
	 * @param  Application  $app
	 *
	 * @since  1.1.0
	 */
	public function setApplication($app)
	{
		$this->app = $app;
	}

	/**
	 * Sets this controllers Request instance
	 *
	 * @param  Request  $request
	 *
	 * @since  1.1.0
	 */
	public function setRequest($request)
	{
		$this->request = $request;
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
		// get the route that got us here
		$this->route = array_shift($args);

		// determine the method to call
		$action = $this->route->action ?: $this->defaultAction;

		if ( ! is_callable(array($this, $method = strtolower($this->route->method).$action)))
		{
			if ( ! is_callable(array($this, $method = $this->actionPrefix.$action)))
			{
				throw new NotFound('FOU-026: No such action ['.$action.'] found in Controller: [\\'.get_class($this).']');
			}
		}

		 // only public methods can be called via a request
		$method = new \ReflectionMethod($this, $method);
		if ( ! $method->isPublic())
		{
			throw new NotFound('FOU-027: Unavailable action ['.$method->name.'] in Controller: [\\'.get_class($this).']');
		}

		// create a response object
		if (empty($this->responseFormat))
		{
			// pick a default, JSON for ajax calls, HTML for non-ajax requests
			$this->responseFormat = $this->request->getInput()->isAjax() ? 'json' : 'html';
		}

		// store the current response format, so we can detect runtime changes
		$this->initialResponseFormat = $this->responseFormat;

		// construct a new response object
		$this->response = $this->factory->createResponseInstance($this->responseFormat, array($this->request));

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
			$response = $this->factory->createResponseInstance($this->responseFormat, array($this->request));
			$this->response = $response->setContent($this->response->getContent());
		}
	}
}
