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
	 * Forge a new configuration object
	 *
	 * @param  $name  name of the object
	 * @returns	Config
	 *
	 * @since  2.0.0
	 */
	public static function forge($name)
	{
		return static::getConfig($name);
	}

	/**
	 * Get a defined configuration object
	 *
	 * @param  $name  name of the configuration object, if not given returns the current application config object
	 * @returns	Config
	 *
	 * @since  2.0.0
	 */
	public static function getConfig($name = null)
	{
		if ($name === null)
		{
			$name = ($app = \Application::getActive()) ? $app->getName() : '__default__';
		}
		return \Dependency::multiton('config', $name);
	}

	/**
	 * Create the global input instance and load all global configuration
	 *
	 * @returns	Config
	 *
	 * @since  2.0.0
	 */
	public static function loadGlobals()
	{
		// get us an instance of input if we don't have one yet
		$config = static::forge('__default__');

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
		return static::getConfig();
	}
}
