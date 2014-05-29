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
 * Pagination Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  1.1.0
 */
class Pagination extends Base
{
	/**
	 * Returns an instance of the Pagination object.
	 *
	 * @return  Fuel\Common\Pagination
	 *
	 * @since  2.0.0
	 */
	public static function forge()
	{
		return static::getDic()->resolve('pagination', func_get_args());
	}

	/**
	 * Get the default instance for this Facade
	 *
	 * @return  Fuel\Common\Pagination
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		return static::forge();
	}
}
