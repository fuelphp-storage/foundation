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

use Fuel\Foundation\Application as AppInstance;
use Fuel\Foundation\Input as InputInstance;

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
	 * Forge a new Input object
	 *
	 * @param  $input  array with input variables
	 *
	 * @returns	Input
	 *
	 * @since  2.0.0
	 */
	public static function forge(AppInstance $app = null, Array $input = array(), InputInstance $parent = null)
	{
		return \Dependency::resolve('input', array($app, $input, $parent));
	}

	/**
	 * Create the global input instance and load all globals
	 *
	 * @since  2.0.0
	 */
	public static function loadGlobals()
	{
		// get us an instance of input if we don't have one yet
		static::$instance or static::$instance = static::forge();

		// and load it with all global data available
		static::$instance->fromGlobals();
	}

	/**
	 * Get the object instance for this Facade
	 *
	 * @returns	Input
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		// get the current request instance
		if ($request = \Request::getInstance())
		{
			return $request->getInput();
		}

		// no active request, return the global instance
		return static::$instance;
	}
}
