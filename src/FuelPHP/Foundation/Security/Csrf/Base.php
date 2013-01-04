<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    FuelPHP\Foundation
 * @version    2.0
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 */

namespace FuelPHP\Foundation\Security\Csrf;

/**
 * Base CSRF Security class
 *
 * Basis for classes dealing with tokens to secure against CSRF attacks.
 *
 * @package  Fuel\Kernel
 *
 * @since  2.0.0
 */
abstract class Base
{
	/**
	 * Fetch the CSRF token to submit the next request
	 *
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	abstract public function getToken();

	/**
	 * Checks if given token is valid, when none given take it from Input
	 *
	 * @param   string  $token
	 * @return  bool
	 *
	 * @since  2.0.0
	 */
	abstract public function checkToken($token = null);

	/**
	 * Updates the token when necessary
	 *
	 * @param   bool  $forceReset
	 * @return  Base
	 *
	 * @since  2.0.0
	 */
	abstract public function updateToken($forceReset = false);
}
