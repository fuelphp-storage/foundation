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
 * Input Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Input extends Base
{
	/**
	 * Forge a new Input object
	 *
	 * @param  $input  array with input variables
	 *
	 * @returns	Input
	 *
	 * @since  2.0.0
	 */
	public static function forge(Array $input = array())
	{
		return static::$dic->resolve('input', func_get_args());
	}

	/**
	 * Get the object instance for this Facade
	 *
	 * @returns	Input
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		// get the current request instance
		if ($request = \Request::getInstance())
		{
			return $request->getInput();
		}

		// no active request, return the current application instance
		return \Application::getInstance()->getInput();
	}
}
