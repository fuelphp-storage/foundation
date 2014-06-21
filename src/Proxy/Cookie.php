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

use Fuel\Foundation\Application as AppInstance;

/**
 * Cookie Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Cookie extends Base
{
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
			return $request->getInput()->getCookie();
		}

		// no active request, return the global one
		return \Component::getInstance()->getInput()->getCookie();
	}
}
