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
use Fuel\Foundation\Application as App;

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
	 * @var  array  List of loaded environments
	 *
	 * @since  2.0.0
	 */
	protected static $environments = array();

	/**
	 * Forge a new environment object
	 *
	 * @param  Application  $app  Application object on which to forge this environment
	 * @param  string  $enviroment  Name of the current environment
	 *
	 * @since  2.0.0
	 */
	public static function forge(App $app, $environment)
	{
		// do we already have this application?
		if (isset(static::$environments[$app->getName()]))
		{
			throw new \InvalidArgumentException('The environment "'.$app->getName().'" is already forged.');
		}

		return static::$environments[$app->getName()] = \Dependency::resolve('environment', array($app, $environment, $app->getConfig()));
	}

	/**
	 * Get a defined environment instance
	 *
	 * @param  $name  name of the environment
	 * @throws InvalidArgumentException if the requested environment does not exist
	 * @returns	Environment
	 *
	 * @since  2.0.0
	 */
	public static function get($name)
	{
		if ( ! isset(static::$environments[$name]))
		{
			throw new \InvalidArgumentException('There is no environment defined named "'.$name.'".');
		}

		return static::$environments[$name];
	}

	/**
	 * Get the object instance for this Facade
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		return null;
	}
}
