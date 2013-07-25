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
 * Dependency Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Dependency extends Base
{
	/**
	 * @var  Whoops\Run
	 *
	 * @since  2.0.0
	 */
	protected static $dic;

	/**
	 * Get the Dependency Container
	 *
	 * @since  2.0.0
	 */
	public static function getDic()
	{
		return static::$dic;
	}

	/**
	 * Set the Dependency Container
	 *
	 * @since  2.0.0
	 */
	public static function setDic($dic = null)
	{
		if ($dic === null)
		{
			// use the framework default Dependency container
			static::$dic = new \Fuel\Dependency\Container;
		}
		else
		{
			// set a custom DiC
			static::$dic = $dic;
		}

		return static::$dic;
	}

	/**
	 * Get the object instance for this Facade
	 *
	 * @since  2.0.0
	 */
	protected static function getInstance()
	{
		return static::$dic;
	}
}
