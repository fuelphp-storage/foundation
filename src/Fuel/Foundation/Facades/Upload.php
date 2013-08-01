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
 * Upload Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Upload extends Base
{
	/**
	 * Create and retrieve an instance.
	 *
	 * @param   string  $name    instance reference
	 *
	 * @return  Fuel\Upload\Upload
	 */
	public static function instance($name = '__default__')
	{
		return \Dependency::multiton('upload', $name);
	}

	/**
	 * Delete an multiton instance from the facade.
	 *
	 * @param  mixed  $name  instance name
	 */
	public static function delete($name)
	{
		return \Dependency::remove('upload::'.$name);
	}

	/**
	 * Get a new Container instance.
	 *
	 * @return  Fuel\Upload\Upload  new Upload instance
	 */
	public static function forge()
	{
		return \Dependency::resolve('upload');
	}

	/**
	 * Get the default instance for this Facade
	 *
	 * @return  Fuel\Upload\Upload
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		return static::instance('__default__');
	}
}
