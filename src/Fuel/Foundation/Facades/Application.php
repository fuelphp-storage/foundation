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
 * Application Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Application extends Base
{
	/**
	 * Forge a new application
	 *
	 * @param  $name  name of the application
	 * @param  $config  array with application configuration information
	 *
	 * @throws InvalidArgumentException if a required config value is missing or incorrect
	 * @throws RuntimeException if the application to forge already exists
	 *
	 * @returns	Application
	 *
	 * @since  2.0.0
	 */
	public static function forge($name, array $config = array())
	{
		// create and return this application instance
		return static::$dic->multiton('application', $name, func_get_args());
	}

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
		if ( ! static::$dic->isInstance('application', $name))
		{
			throw new \RuntimeException('There is no application defined named "'.$name.'".');
		}

		// return the application instance
		return static::$dic->multiton('application', $name);
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
		$stack = static::$dic->resolve('requeststack');
		if ($request = $stack->top())
		{
			$app = $request->getApplication();
		}
		else
		{
			// fall back to the main application
			$app = static::$dic->resolve('application.main');
		}

		return $app;
	}
}
