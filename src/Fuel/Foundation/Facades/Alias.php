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
 * Alias Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Alias extends Base
{
	/**
	 * @var  Fuel\Alias\Manager
	 *
	 * @since  2.0.0
	 */
	protected static $manager;

	/**
	 * Get the Alias Manager
	 *
	 * @since  2.0.0
	 */
	public static function getManager()
	{
		return static::$manager;
	}

	/**
	 * Set the Alias Manager
	 *
	 * @since  2.0.0
	 */
	public static function setManager($manager = null)
	{
		if ($manager === null)
		{
			// use the framework default Alias Manager
			static::$manager = new \Fuel\Alias\Manager;
			static::$manager->register();
		}
		else
		{
			// set a custom Alias Manager
			static::$manager = $manager;
		}

		return static::$manager;
	}

	/**
	 * Get the object instance for this Facade
	 *
	 * @since  2.0.0
	 */
	protected static function getInstance()
	{
		return static::$manager;
	}
}
