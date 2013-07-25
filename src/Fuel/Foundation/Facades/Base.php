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
 * Base Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
abstract class Base
{
	/**
	* Static method interface on dynamic objects
	*
	* @param  string  $method  method to call on the instance
	* @param  array  $args  arguments to be passed to it
	* @return  mixed
	*/
	public static function __callStatic($method, $args)
	{
		// get the instance to call the method on
		if ( ! $instance = static::getInstance())
		{
			throw new \ErrorException('No instance available to call: '.get_called_class().'::'.$method.'()');
		}

		// calling the method directly is faster then call_user_func_array() !
		switch (count($args))
		{
			case 0:
				return $instance->$method();

			case 1:
				return $instance->$method($args[0]);

			case 2:
				return $instance->$method($args[0], $args[1]);

			case 3:
				return $instance->$method($args[0], $args[1], $args[2]);

			case 4:
				return $instance->$method($args[0], $args[1], $args[2], $args[3]);

			default:
				return call_user_func_array(array($instance, $method), $args);
		}
	}

}
