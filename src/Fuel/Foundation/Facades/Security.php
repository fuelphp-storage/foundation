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

use Fuel\Foundation\Application as AppInstance;

/**
 * Security Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  1.0.0
 */
class Security extends Base
{
	/**
	 * Forge a new security object
	 *
	 * @param  Application  $app  Application object on which to forge this security object
	 *
	 * @returns	Security
	 *
	 * @since  2.0.0
	 */
	public static function forge(AppInstance $app)
	{
		// do we already have this instance?
		$name = $app->getName();
		if (\Dependency::isInstance('security', $name))
		{
			throw new \RuntimeException('The security object "'.$name.'" is already forged.');
		}

		return \Dependency::multiton('security', $name, array($app));
	}

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
		if ($app = \Application::getInstance())
		{
			return \Dependency::multiton('security', $app->getName(), array($app));
		}

		// no active application, so no instance available
		return null;
	}
}
