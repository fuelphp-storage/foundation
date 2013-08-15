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
	public static function forge(AppInstance $app, $environment)
	{
		// do we already have this instance?
		$name = $app->getName();
		if (\Dependency::isInstance('environment', $name))
		{
			throw new \RuntimeException('The environment "'.$name.'" is already forged.');
		}

		return \Dependency::multiton('environment', $name, array($app, $environment, $app->getConfig()));
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
		if ( ! \Dependency::isInstance('environment', $name))
		{
			throw new \InvalidArgumentException('There is no environment defined named "'.$name.'".');
		}

		return \Dependency::multiton('environment', $name);
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

		return null;
	}
}
