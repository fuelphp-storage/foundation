<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
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
	 * Get a new Event Queue instance.
	 *
	 * @return  Fuel\Event\Queue  new Event Queue instance
	 */
	public static function forge()
	{
		if ($name === null)
		{
			$name = uniqid(true);
		}

		return static::getDic()->multiton('queue', $name);
	}

	/**
	 * Delete an multiton instance from the facade.
	 *
	 * @param  mixed  $name  instance name
	 */
	public static function delete($name)
	{
		return static::getDic()->remove('queue::'.$name);
	}

	/**
	 * Get an instance for this Facade
	 *
	 * @return  Fuel\Event\Queue
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		return static::forge();
	}
}
