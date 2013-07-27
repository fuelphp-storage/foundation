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
	 * @var  Container  the DiC
	 *
	 * @since  2.0.0
	 */
	protected static $dic;

	/**
	 * Initialization, set the Dependency Container we're going to use
	 *
	 * @since  2.0.0
	 */
	public static function initialize($dic = null)
	{
		if ($dic)
		{
			static::$dic = $dic;
		}
		else
		{
			static::$dic = new \Fuel\Dependency\Container;

			// register the DiC by itself so it can be resolved
			static::$dic->registerSingleton('dic', function($container)
			{
				return $container;
			});
		}

		return static::getInstance();
	}

	/**
	 * Get the object instance for this Facade
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		return static::$dic;
	}
}
