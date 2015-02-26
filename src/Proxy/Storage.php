<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation\Proxy;

/**
 * Storage Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Storage extends Base
{
	/**
	 * Forge a new storage object, or return an existing one
	 *
	 * @param  string  $type  type of storage object requested
	 * @param  mixed   $config  name of a storage config definition, or a configuration array
	 *
	 * @throws InvalidArgumentException if a required config value is missing or incorrect
	 * @throws RuntimeException if the application to forge already exists
	 *
	 * @returns	Mixed  An instance of the requested storage type
	 *
	 * @since  2.0.0
	 */
	public static function forge($type = 'db', $config = null)
	{
		// validate the type
		if (empty($type) or ! is_string($type))
		{
			throw new \RuntimeException('FOU-028: Specified storage type must be a string value');
		}

		// create and return the requested storage instance
		return static::getDic()->get('storage.'.$type, array($config));
	}

	/**
	 * Alias for forge('db')
	 */
	public static function db($config = null)
	{
		return static::forge('db', $config);
	}

	/**
	 * Alias for forge('memcached')
	 */
	public static function memcached($config = null)
	{
		return static::forge('memcached', $config);
	}
}
