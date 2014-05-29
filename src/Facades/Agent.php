<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
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
	public static function forge($name = null)
	{
		// get the current application name via the active request instance
		if ( ! $name)
		{
			$name = \Application::getInstance()->getName();
		}

		// get the arguments, and remove the name
		$args = func_get_args();
		array_shift($args);

		return static::getDic()->multiton('agent', $name, $args);
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
