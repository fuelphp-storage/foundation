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
	 * Returns an instance of a Request.
	 *
	 * @param   string  URI to request
	 * @param   array   optional array of input variables
	 * @param   string  type of request instance required, null to autodetect
	 *
	 * @return  Request\Base
	 */
	public static function forge($resource, Array $input = array(), $type = null)
	{
		return static::$dic->resolve('request', func_get_args());
	}

	/**
	 * Check if the current request is the main request
	 *
	 * @return  bool  Whether or not this is the main request
	 *
	 * @since  2.0.0
	 */
	public static function isMainRequest()
	{
		$stack = Dependency::resolve('requeststack');
		return count($stack) === 1;
	}

	/**
	 * Check if the current request is an HMVC request
	 *
	 * @return  bool  Whether or not this is an HMVC request
	 *
	 * @since  2.0.0
	 */
	public static function isHMVCRequest()
	{
		$stack = Dependency::resolve('requeststack');
		return count($stack) !== 1;
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
		$stack = Dependency::resolve('requeststack');
		return $stack->top();
	}
}
