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
	 * Cleans the request URI
	 *
	 * @param  string  $uri     uri to clean
	 * @param  bool    $strict  whether to remove relative directories
	 *
	 * @since  1.0.0
	 */
	public static function cleanUri($uri, $strict = false)
	{
		return $uri;
	}

	/**
	 * Get the object instance for this Facade
	 *
	 * @since  2.0.0
	 */
	protected static function getInstance()
	{
		return null;
	}
}
