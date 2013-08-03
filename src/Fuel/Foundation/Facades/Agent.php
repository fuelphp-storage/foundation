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
 * Agent Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Agent extends Base
{
	/**
	 * default instance
	 */
	protected static $instance;

	/**
	 * Get a new Agent instance.
	 *
	 * @return  Fuel\Agent\Agent  new Agent instance
	 */
	public static function forge($name = '__default__', Array $config = array(), $method = 'browscap')
	{
		// get the current application name via the active request instance
		if ($name === '__default__' and $request = \Request::getInstance())
		{
			$name = $request->getApplication()->getName();
		}

		// fetch the application agent config, and merge it with the one passed
		$config = array_merge(\Config::load('agent', true), $config);
		return \Dependency::multiton('agent', $name, array($config, $method));
	}

	/**
	 * Get the object instance for this Facade
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		return static::forge();
	}
}

