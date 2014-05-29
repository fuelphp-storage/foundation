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
	 * Get a new Container instance.
	 *
	 * @return  Fuel\Upload\Upload  new Upload instance
	 */
	public static function forge()
	{
		if ($name === null)
		{
			$name = uniqid(true);
		}

		return static::getDic()->multiton('upload', $name);
	}

	/**
	 * Delete an multiton instance from the facade.
	 *
	 * @param  mixed  $name  instance name
	 */
	public static function delete($name)
	{
		return static::getDic()->remove('upload::'.$name);
	}


	/**
	 * Get an instance for this Facade
	 *
	 * @return  Fuel\Upload\Upload
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		return static::forge();
	}
}
