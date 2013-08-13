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
use Fuel\Foundation\Request\Base as RequestBase;

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
	 * Forge a new request object
	 *
	 * @param  string  $resource  The resource to request
	 * @param  array   $input  Optional custom input for this request
	 * @param  string  $type  Type of request to load
	 *
	 * @since  2.0.0
	 */
	public static function forge($resource, Array $input = array(), $type = null)
	{
		$app = \Application::getInstance();

		// if no type is given, auto-detect the type
		if ($type === null)
		{
			$url = parse_url($resource = rtrim($resource, '/').'/');

			// determine the type of request
			if (empty($resource) or empty($url['host']) or substr($resource,0,1) == '/')
			{
				// URI only, so it's an local request
				$resource  = '/'.trim(strval($resource), '/');
				$type = '.local';
			}
			else
			{
				// http request for this current base url?
				if (strpos($resource, $app->getEnvironment()->getBaseUrl()) === 0)
				{
					// request for the current base URL, so it's a local request too
					$resource  = empty($url['path']) ? '/' : $url['path'];
					$type = '.local';
				}
				else
				{
					// external URL, use the Curl request driver
					$type = '.curl';
				}
			}
		}
		elseif (is_string($type) and ! empty($type) and substr($type,0,1) !== '.')
		{
			$type = '.'.$type;
		}
		else
		{
			// default to local
			$type = '.local';
		}

		return \Dependency::resolve('request'.$type, array($app, $resource, $input));
	}

	/**
	 * get the current active request
	 *
	 * @return  RequestInstance
	 *
	 * @since  2.0.0
	 */
	public static function isMainRequest()
	{
		// if we only have one request on the stack, we're in the main request
		return count(static::$requestStack) === 1;
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
	public static function setActive(RequestBase $request = null)
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
