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

use Fuel\Config\Container;
use Fuel\Foundation\Application as AppInstance;

/**
 * Environment Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Environment extends Base
{
	/**
	 * Forge a new environment object
	 *
	 * @param  Application  $app  Application object on which to forge this environment
	 * @param  string  $enviroment  Name of the current environment
	 *
	 * @throws RuntimeException if the environment to forge already exists
	 *
	 * @returns	Environment
	 *
	 * @since  2.0.0
	 */
	public static function forge($environment)
	{
		$name = \Application::getInstance()->getName();
		return static::$dic->multiton('environment', $name, func_get_args());
	}

	/**
	 * Get a defined environment instance
	 *
	 * @param  $name  name of the environment
	 *
	 * @throws RuntimeException if the environment to get does not exist
	 *
	 * @returns	Environment
	 *
	 * @since  2.0.0
	 */
	public static function get($name)
	{
		if ( ! static::$dic->isInstance('environment', $name))
		{
			throw new \InvalidArgumentException('There is no environment defined named "'.$name.'".');
		}

		return static::$dic->multiton('environment', $name);
	}

	/**
	 * Get the object instance for this Facade
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		// get the current environment via the active request instance
		if ($request = \Request::getActive())
		{
			return $request->getApplication()->getEnvironment();
		}

		// no active request, return the main applications' environment
		return \Application::getInstance()->getEnvironment();
	}
}
