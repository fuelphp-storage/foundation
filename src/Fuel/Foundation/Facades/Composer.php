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
 * Composer Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Composer extends Base
{
	/**
	 * @var  Composer\Autoload\ClassLoader
	 *
	 * @since  2.0.0
	 */
	protected static $loader;

	/**
	 * get the Composer autoloader instance
	 *
	 * @since  2.0.0
	 */
	public static function getLoader()
	{
		return static::$loader;
	}

	/**
	 * Set the Composer autoloader instance
	 *
	 * @since  2.0.0
	 */
	public static function setLoader($autoloader)
	{
		// store the composer autoloader instance
		static::$loader = $autoloader;

		return static::$loader;
	}

	/**
	 * Get the object instance for this Facade
	 *
	 * @since  2.0.0
	 */
	protected static function getInstance()
	{
		return static::$loader;
	}
}
