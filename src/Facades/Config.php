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
 * Config Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Config extends Base
{
	/**
	 * Get the object instance for this Facade
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		// get the current request instance
		if ($request = \Request::getInstance())
		{
			return $request->getConfig();
		}

		// no active request, return the current components instance
		return \Component::getInstance()->getConfig();
	}
}
