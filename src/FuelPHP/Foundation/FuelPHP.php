<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    Fuel\Core
 * @version    2.0.0
 * @license    MIT License
 * @copyright  2010 - 2012 Fuel Development Team
 */

namespace FuelPHP\Foundation;

/**
 * FuelPHP class
 *
 * The FuelPHP class provides a static entry into the framework, and allows
 * easy access to commonly used components
 *
 * @package  FuelPHP\Foundation
 *
 * @since  1.0.0
 */
class FuelPHP
{
	/**
	 * @var bool
	 *
	 * @since  2.0.0
	 */
	protected static $started;

	/**
	 * @var  FuelPHP\DependencyInjection\Container
	 *
	 * @since  2.0.0
	 */
	protected static $dic;

	/**
	 * @var  FuelPHP\Alias\Manager
	 *
	 * @since  2.0.0
	 */
	protected static $alias;

	/**
	 * @var  Environment
	 *
	 * @since  2.0.0
	 */
	protected static $env;

	/**
	 * Constructor
	 *
	 * @since  2.0.0
	 */
	protected function __construct()
	{
	}

	/**
	 * Initialize the class
	 *
	 * @since  2.0.0
	 */
	public static function kickstart()
	{
		// make sure we run only once
		if ( ! static::$started)
		{
			// setup the DiC
			static::$dic = new \FuelPHP\DependencyInjection\Container;

			// setup the alias manager
			static::$alias = static::$dic->resolve('FuelPHP\Alias\Manager')->register();

			// mark the initialisation complete
			static::$started = true;
		}
	}

	/**
	 * Alias for resolve
	 *
	 * @since  2.0.0
	 */
	public static function forge($args)
	{
		return call_user_func_array(array(static::$dic, 'resolve'), func_get_args());
	}

	/**
	 * Facade for Dic::resolve()
	 *
	 * @since  2.0.0
	 */
	public static function resolve($args)
	{
		return call_user_func_array(array(static::$dic, 'resolve'), func_get_args());
	}

	/**
	 * Facade for Dic::register()
	 *
	 * @since  2.0.0
	 */
	public static function register($args)
	{
		return call_user_func_array(array(static::$dic, 'register'), func_get_args());
	}

	/**
	 * Facade for Alias::alias()
	 *
	 * @since  2.0.0
	 */
	public static function alias($args)
	{
		return call_user_func_array(array(static::$alias, 'alias'), func_get_args());
	}

		/**
	 * Facade for Alias::alias()
	 *
	 * @since  2.0.0
	 */
	public static function aliasNamespace($args)
	{
		return call_user_func_array(array(static::$alias, 'aliasNamespace'), func_get_args());
	}

	/**
	 * Facade on the framework core classes
	 *
	 * @since  2.0.0
	 */
	public static function __callStatic($method, $args)
	{
// CHECKME - for now capture and dump undefined static calls
var_dump(func_get_args());die();
	}
}
