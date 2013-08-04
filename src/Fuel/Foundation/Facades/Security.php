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
		$name = $app->getName();
		return \Dependency::multiton('security', $name, array($app));
	}

	/**
	 * Generate a unique CSRF token for the given form identification
	 *
	 * @param  string  $form_id  Unique identification of the form to protect
	 *
	 * @since  2.0.0
	 */
	public static function getCsrfToken($form_id)
	{
		return static::getInstance()->csrf()->getToken($form_id);
	}

	/**
	 * Validate a given CSRF token
	 *
	 * @param  string  $form_id  Unique identification of the form to protect
	 * @param  string  $token    Token to validate
	 *
	 * @since  2.0.0
	 */
	public static function validateCsrfToken($form_id, $token)
	{
		return static::getInstance()->csrf()->validateToken($form_id, $token);
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
