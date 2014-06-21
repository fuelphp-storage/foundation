<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation\Proxy;

/**
 * Num Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  1.0.0
 */
class Num extends Base
{
	/**
	 * Returns an instance of the Num object.
	 *
	 * @return  Fuel\Common\Num
	 *
	 * @since  2.0.0
	 */
	public static function forge()
	{
		return static::getDic()->resolve('num', func_get_args());
	}

	/**
	 * Get the default instance for this Facade
	 *
	 * @return  Fuel\Common\Num
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		return static::forge();
	}
}
