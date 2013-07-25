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
 * Input Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Input extends Base
{
	/**
	 * @var  \Fuel\Foundation\Input the global input instance
	 *
	 * @since  2.0.0
	 */
	protected static $instance;

	/**
	 * Create the global input instance and load all globals
	 *
	 * @since  2.0.0
	 */
	public static function loadGlobals()
	{
		// get us an instance of input if we don't have one yet
		static::$instance or static::$instance = \Dependency::resolve('input', array(null));

		// and load it with all global data available
		static::$instance->fromGlobals();
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
