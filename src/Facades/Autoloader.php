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
 * Autoloader, a Facade class on the Composer loader instance
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Autoloader extends Base
{
	/**
	 * Get the object instance for this Facade
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		return static::getDic()->resolve('autoloader');
	}
}
