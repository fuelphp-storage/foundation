<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation\Facades;

use Fuel\Foundation\Application as AppInstance;
use Fuel\Foundation\Request as RequestInstance;

/**
 * Request Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  1.0.0
 */
class Request extends Base
{
	/**
	 * @var  array  active requests stack
	 *
	 * @since  2.0.0
	 */
	protected static $requestStack;

	/**
	 * @var  RequestInstance  last active request
	 *
	 * @since  2.0.0
	 */
	protected static $request;

	/**
	 * Forge a new environment object
	 *
	 * @param  Application  $app  Application object on which to forge this environment
	 * @param  string  $enviroment  Name of the current environment
	 *
	 * @since  2.0.0
	 */
	public static function forge(AppInstance $app, $uri, Array $input = array())
	{
		return \Dependency::resolve('request', array($app, $uri, $input));
	}

	/**
	 * get the current active request
	 *
	 * @return  RequestInstance
	 *
	 * @since  2.0.0
	 */
	public static function getActive()
	{
		return static::$request;
	}

	/**
	 * Sets the current active request
	 *
	 * @param   Request  $request
	 *
	 * @return  RequestInstance
	 *
	 * @since  2.0.0
	 */
	public static function setActive(RequestInstance $request = null)
	{
		static::$requestStack->push($request);
		static::$request = $request;
		return $request;
	}

	/**
	 * Resets the current active request
	 *
	 * @return  bool  true if the reset was succesful, false if this was the main request
	 *
	 * @since  2.0.0
	 */
	public static function resetActive()
	{
		if ( ! static::$requestStack->isEmpty())
		{
			static::$requestStack->pop();
			return true;
		}
		return false;
	}

	/**
	 * Returns current active Request
	 *
	 * @return  Request
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		if (static::$requestStack === null)
		{
			static::$requestStack = new \SplStack();
		}

		return static::$requestStack->isEmpty() ? null : static::$requestStack->top();
	}
}
