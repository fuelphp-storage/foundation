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
 * Application Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Application extends Base
{
	/**
	 * Get a defined application instance
	 *
	 * @param  $name  name of the application
	 *
	 * @throws RuntimeException if the application to get does not exist
	 *
	 * @returns	Application
	 *
	 * @since  2.0.0
	 */
	public static function get($name)
	{
		// make sure we have this application instance
		if ( ! static::getDic()->isInstance('application', $name))
		{
			throw new \RuntimeException('FOU-014: There is no application defined named ['.$name.'].');
		}

		// return the application instance
		return static::getDic()->multiton('application::'.$name);
	}

	/**
	 * Get the object instance for this Facade
	 *
	 * @return  Application
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		// get the current requests' application object
		return static::getDic()->get('applicationInstance', [false]);
	}
}
