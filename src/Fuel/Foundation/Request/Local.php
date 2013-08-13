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
	public function __construct($app, $resource = '', $input = null)
	{
		parent::__construct($app, $resource, $input);

		// make sure the request has the correct format
		$this->request  = '/'.trim(strval($resource), '/');
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

		// get a route object for this request
		$this->route = \Router::translate($this->request, \Input::getInstance()->getMethod() );

		// log the request destination
		\Log::info($this->route->method.' request routed to '.$this->route->translation);

		// store the request parameters
		$this->params = array_merge($this->params, $this->route->parameters);

		// push any remaining segments so they'll be available as action arguments
		if ( ! empty($this->route->segments))
		{
			$this->route->parameters = array_merge($this->route->parameters, $this->route->segments);
		}

		try
		{
			if (empty($this->route->controller))
			{
				throw new NotFound('No route match has been found for this request.');
			}

			$controller = new $this->route->controller;
			if ( ! is_callable($controller))
			{
				throw new NotFound('The Controller returned by routing is not callable. Does it extend a base controller?');
			}

			// push the route so we have access to it in the controller
			array_unshift($this->route->parameters, $this->route);

			// add the root path to the config, lang and view manager objects
			$app = \Application::getInstance();
			$app->getViewManager()->getFinder()->addPath($this->route->path);
			$app->getConfig()->addPath($this->route->path.'config'.DS);
			$app->getLanguage()->addPath($this->route->path.'lang'.DS.\Lang::getActive().DS);

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
				// rethrow
				throw $e;
			}

			// make sure we got a proper response object back
			if ( ! $this->response instanceOf \Fuel\Foundation\Response\Base)
			{
				throw new \DomainException('A Controller must return a Response object that extends \Fuel\Foundation\Response\Base.');
			}
		}
		catch (\Exception $e)
		{
			// log the request termination
			\Log::info('Request executed, but failed: '.$e->getMessage());

			// reset and rethrow
			\Request::resetActive();
			throw $e;
		}

		// remove the root path to the config, lang and view manager objects
		$app->getLanguage()->removePath($this->route->path.'lang'.DS.\Lang::getActive().DS);
		$app->getConfig()->removePath($this->route->path.'config'.DS);
		$app->getViewManager()->getFinder()->removePath($this->route->path);

		// log the request termination
		\Log::info('Request executed');

		\Request::resetActive();

		return $this;
	}
}
