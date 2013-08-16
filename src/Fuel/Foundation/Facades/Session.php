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
 * Session Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Session extends Base
{
	/**
	 * Produces fully configured session driver instances
	 *
	 * @param	array|string  $config	full driver config, a driver name or a driver object
	 */
	public static function forge($config = array())
	{
		// create the session manager
		$manager = \Dependency::resolve('session', func_get_args());

		// if the current application doesn't have a default session
		if ( ! \Application::getInstance()->getSession())
		{
			// assign it to the application
			Application::getInstance()->setSession($manager);
		}

		// return the forged session manager instance
		return $manager;
	}

	/**
	 * Get the object instance for this Facade
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		// get the current session via the active request instance
		if ($request = \Request::getInstance())
		{
			return $request->getApplication()->getSession();
		}

		return null;
	}
}
