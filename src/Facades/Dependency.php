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

use Fuel\Dependency\Container;

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
	 * setup the Dependency Container we're going to use
	 *
	 * @since  2.0.0
	 */
	public static function setup($dic = null)
	{
		// if a custom DiC is passed, use that
		if ($dic)
		{
			static::$dic = $dic;
		}

		// else set one up if not done yet
		elseif ( ! static::$dic)
		{
			// get us a Dependency Container instance
			static::$dic = new Container;

			// register the DiC on classname so it can be auto-resolved
			static::$dic->registerSingleton('Fuel\Dependency\Container', function($container)
			{
				return $container;
			});

		}

		// regitser the dic manual resolving
		static::$dic->registerSingleton('dic', function($container)
		{
			return $container;
		});

		return static::$dic;
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
