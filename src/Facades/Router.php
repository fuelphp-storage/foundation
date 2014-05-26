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
 * Router Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  1.0.0
 */
class Router extends Base
{
	/**
	 * Get the object instance for this Facade
	 *
	 * @returns	Fuel/Routing/Router
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		return \Application::getInstance()->getRouter();
	}
}
