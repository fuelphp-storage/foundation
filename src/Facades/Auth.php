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
 * Auth Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Auth extends Base
{
	/**
	 * @var  Fuel\Auth\Manager  this first instance forged will be the default instance
	 */
	protected static $instance;

	/**
	 * Forge a new auth instance
	 *
	 * @param  string|array  $name    name of the instance, or the config array
	 * @param  array         $config  last-minute runtime configuration
	 *
	 * @returns	Fuel\Auth\Manager
	 *
	 * @since  2.0.0
	 */
	public static function forge($name = 'default', array $config = array())
	{
		// deal with only passing an array
		if (is_array($name))
		{
			$config = $name;
			$name = empty($config['name']) ? 'default' : $config['name'];
		}

		// make sure we don't already have this auth instance
		if (static::$dic->isInstance('auth', $name))
		{
			throw new \RuntimeException('FOU-036: An auth instance named ['.$name.'] is already defined.');
		}

		// create the instance
		$instance = static::$dic->resolve('auth', array($name, $config));

		// if this is the first, make it the default instance
		if ( ! static::$instance)
		{
			static::$instance = $instance;
		}

		return $instance;
	}

	/**
	 * Get the default instance for this Facade
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		if ( ! static::$instance)
		{
			return static::forge();
		}

		return static::$instance;
	}
}
