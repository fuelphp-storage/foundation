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
	public static function forge()
	{
		return static::getDic()->multiton('security', \Application::getInstance()->getName(), func_get_args());
	}

	/**
	 * Generate a unique CSRF token for the given form identification
	 *
	 * @param  string  $id  Unique identification of the object to protect
	 *
	 * @since  2.0.0
	 */
	public static function getCsrfToken($id)
	{
		return static::getInstance()->csrf()->getToken($id);
	}

	/**
	 * Validate a given CSRF token
	 *
	 * @param  string  $id     Unique identification of the object to protect
	 * @param  string  $token  Token to validate
	 *
	 * @since  2.0.0
	 */
	public static function validateCsrfToken($id, $token)
	{
		return static::getInstance()->csrf()->validateToken($id, $token);
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
		// get the application instance
		return static::forge();
	}
}
