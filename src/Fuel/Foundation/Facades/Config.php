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
 * Config Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Config extends Base
{
	/**
	 * @var  array  List of loaded configuration objects
	 *
	 * @since  2.0.0
	 */
	protected static $configs = array();

	/**
	 * Forge a new configuration object
	 *
	 * @param  $name  name of the object
	 * @throws InvalidArgumentException if the object already exists
	 * @returns	Application
	 *
	 * @since  2.0.0
	 */
	public static function forge($name)
	{
		// do we already have this object?
		if (isset(static::$configs[$name]))
		{
			throw new \InvalidArgumentException('The configuration object "'.$name.'" is already forged.');
		}

		return static::$configs[$name] = \Dependency::resolve('config');
	}

	/**
	 * Get a defined configuration object
	 *
	 * @param  $name  name of the configuration object
	 * @throws InvalidArgumentException if the requested configuration object does not exist
	 * @returns	Application
	 *
	 * @since  2.0.0
	 */
	public static function get($name = '')
	{
		if ( ! isset(static::$configs[$name]))
		{
			throw new \InvalidArgumentException('There is no configuration object defined named "'.$name.'".');
		}

		return static::$configs[$name];
	}

	/**
	 * Create the global input instance and load all global configuration
	 *
	 * @since  2.0.0
	 */
	public static function loadGlobals()
	{
		// get us an instance of input if we don't have one yet
		$config = static::forge('');

		// load the global default config
		$config->addPath(APPSPATH);
		$config->load('config', null);

		return $config;
	}

	/**
	 * Get the object instance for this Facade
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		die('Config::getInstance() not implemented yet');
	}
}
