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

use Fuel\Dependency\Container;
use \Fuel\Dependency\ResolveException;

use Fuel\Foundation\InjectionFactory;

/**
 * Request injection factory, provides methods to allow the Request
 * class to construct or access new external objects without creating
 * dependencies
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */

class RequestInjectionFactory extends InjectionFactory
{
	/**
	 * Set a Request as the active request
	 */
	public function setActiveRequest($request)
	{
		$stack = $this->container->resolve('requeststack');
		$stack->push($request);
	}

	/**
	 * Pop the current active request from the stack
	 */
	public function resetActiveRequest()
	{
		$stack = $this->container->resolve('requeststack');
		$stack->pop();
	}

	/**
	 * Check if the current request is the main request
	 *
	 * @return  bool  Whether or not this is the main request
	 *
	 * @since  2.0.0
	 */
	public function isMainRequest()
	{
		$stack = $this->container->resolve('requeststack');
		return count($stack) === 1;
	}

	/**
	 * get the current active request
	 *
	 * @return  RequestInstance
	 *
	 * @since  2.0.0
	 */
	public function createControllerInstance($controller)
	{
		$this->container->register('controller', $controller);
		$this->container->extend('controller', 'getApplicationInstance');
		$this->container->extend('controller', 'getRequestInstance');

		return $this->container->resolve('controller');
	}
}
