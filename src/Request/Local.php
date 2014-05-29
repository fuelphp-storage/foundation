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

use Fuel\Foundation\Exception\NotFound;

/**
 * FuelPHP local URI Request class
 *
 * executes a request to a local controller
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Local extends Base
{
	/**
	 * Constructor
	 *
	 * @param  string  $resource
	 * @param  array|Input  $input
	 *
	 * @since  1.0.0
	 */
	public function __construct($component, $resource = '', $inputInstance = null, RequestInjectionFactory $factory)
	{
		parent::__construct($component, $resource, $inputInstance, $factory);

		// make sure the request has the correct format
		$this->request  = '/'.trim(strval($resource), '/');
	}

	/**
	 * Returns this requests current Config object
	 *
	 * @return  Uri
	 *
	 * @since  1.1.0
	 */
	public function getConfig()
	{
		return $this->config;
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
		$this->factory->setActiveRequest($this);

		// log the request
		$this->log->info('Executing request');

		// get a route object for this request
		$this->route = $this->router->translate($this->request, $this->input->getMethod());

		// create a URI object
		$this->uri = $this->factory->createUriInstance($this->route->uri);

		// log the request destination
		$this->log->info($this->route->method.' request routed to '.(is_callable($this->route->translation) ? 'Closure' : $this->route->translation));

		// store the request parameters
		$this->params = array_merge($this->params, $this->route->parameters);

		// push any remaining segments so they'll be available as action arguments
		if ( ! empty($this->route->segments))
		{
			$this->route->parameters = array_merge($this->route->parameters, $this->route->segments);
		}

		if (empty($this->route->controller))
		{
			throw new NotFound('No route match has been found for this request.');
		}

		if (is_callable($this->route->controller))
		{
			$controller = $this->route->controller;
		}
		else
		{
			$controller = $this->factory->createControllerInstance($this->route->controller);
		}

		if ( ! is_callable($controller))
		{
			throw new NotFound('The Controller returned by routing is not callable. Does it extend a base controller?');
		}

		// push the route so we have access to it in the controller
		array_unshift($this->route->parameters, $this->route);

		// add the root path to the config, lang and view manager objects
		if (isset($this->route->path))
		{
			$this->component->getApplication()->getViewManager()->getFinder()->addPath($this->route->path);
		}

		try
		{
			$this->response = call_user_func($controller, $this->route->parameters);
		}
		catch (Exception\Base $e)
		{
			$this->response = $this->errorResponse($e);
		}
		catch (\Exception $e)
		{
			// log the request termination
			$this->log->info('Request executed, but failed: '.$e->getMessage());

			// rethrow
			throw $e;
		}

		// make sure we got a proper response object back
		if ( ! $this->response instanceOf \Fuel\Foundation\Response\Base)
		{
			throw new \DomainException('FOU-024: A Controller must return a Response object that extends "\Fuel\Foundation\Response\Base".');
		}

		// remove the root path to the config, lang and view manager objects
		if (isset($this->route->path))
		{
			$this->component->getApplication()->getViewManager()->getFinder()->removePath($this->route->path);
		}

		// log the request termination
		$this->log->info('Request executed');

		$this->factory->resetActiveRequest();

		return $this;
	}
}
