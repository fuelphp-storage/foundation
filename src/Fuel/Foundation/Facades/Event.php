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
	 * Get a new Container instance.
	 *
	 * @return  Fuel\Event\Container  new Event Container instance
	 */
	public static function forge()
	{
		return \Dependency::resolve('event');
	}

	/**
	 * Get the object instance for this Facade
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		// get the current event manager via the active request instance
		if ($request = \Request::getInstance())
		{
			return $request->getApplication()->getEvent();
		}

		return null;
	}
}
