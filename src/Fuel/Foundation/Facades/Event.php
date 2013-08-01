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

use Fuel\Event\Container;

/**
 * Event Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Event extends Base
{
	/**
	 * Create and retrieve an instance.
	 *
	 * @param   string  $name    instance reference
	 *
	 * @return  Fuel\Event\Container
	 */
	public static function instance($name = '__default__')
	{
		return \Dependency::multiton('event', $name);
	}

	/**
	 * Delete an multiton instance from the facade.
	 *
	 * @param  mixed  $name  instance name
	 */
	public static function delete($name)
	{
		return \Dependency::remove('event::'.$name);
	}

	/**
	 * Get a new Container instance.
	 *
	 * @return  Fuel\Event\Container  new Event Container instance
	 */
	public static function forge()
	{
		return \Dependency::resolve('event');
	}

	/**
	 * Get the default instance for this Facade
	 *
	 * @return  Fuel\Event\Container
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		return static::instance('__default__');
	}
}
