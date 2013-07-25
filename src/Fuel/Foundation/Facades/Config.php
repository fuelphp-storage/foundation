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
	 * @var  \Fuel\Config\Container the global config instance
	 *
	 * @since  2.0.0
	 */
	protected static $instance;

	/**
	 * Create the global input instance and load all globals
	 *
	 * @since  2.0.0
	 */
	public static function loadConfig()
	{
		// get us an instance of input if we don't have one yet
		static::$instance or static::$instance = \Dependency::resolve('config');

		// load the global default config
		static::$instance->addPath(APPSPATH);
		static::$instance->load('config', null);

		return static::$instance;
	}

	/**
	 * Get the object instance for this Facade
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		return static::$instance;
	}
}
