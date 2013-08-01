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
 * Queue Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Queue extends Base
{
	/**
	 * Create and retrieve an instance.
	 *
	 * @param   string  $name    instance reference
	 *
	 * @return  Fuel\Event\Queue
	 */
	public static function instance($name = '__default__')
	{
		return \Dependency::multiton('queue', $name);
	}

	/**
	 * Delete an multiton instance from the facade.
	 *
	 * @param  mixed  $name  instance name
	 */
	public static function delete($name)
	{
		return \Dependency::remove('queue::'.$name);
	}

	/**
	 * Get a new Event Queue instance.
	 *
	 * @return  Fuel\Event\Queue  new Event Queue instance
	 */
	public static function forge()
	{
		return \Dependency::resolve('queue');
	}

	/**
	 * Get the default instance for this Facade
	 *
	 * @return  Fuel\Event\Queue
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		return static::instance('__default__');
	}
}
