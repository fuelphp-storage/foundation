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
 * Cookie Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Cookie extends Base
{
	/**
	 * Gets the value of a signed cookie. Cookies without signatures will not
	 * be returned. If the cookie signature is present, but invalid, the cookie
	 * will be deleted.
	 *
	 *     // Get the "theme" cookie, or use "blue" if the cookie does not exist
	 *     $theme = \Cookie::get('theme', 'blue');
	 *
	 * @param   string  cookie name
	 * @param   mixed   default value to return
	 *
	 * @return  string
	 *
	 * @since 1.0.0
	 */
	public static function get($name = null, $default = null)
	{
		return static::getInstance()->get($name, $default);
	}

	/**
	 * Sets a signed cookie. Note that all cookie values must be strings and no
	 * automatic serialization will be performed!
	 *
	 *     // Set the "theme" cookie
	 *     \Cookie::set('theme', 'red');
	 *
	 * @param   string    name of cookie
	 * @param   string    value of cookie
	 * @param   integer   lifetime in seconds
	 * @param   string    path of the cookie
	 * @param   string    domain of the cookie
	 * @param   boolean   if true, the cookie should only be transmitted over a secure HTTPS connection
	 * @param   boolean   if true, the cookie will be made accessible only through the HTTP protocol
	 *
	 * @since 1.0.0
	 */
	public static function set($name, $value, $expiration = null, $path = null, $domain = null, $secure = null, $http_only = null)
	{
		// get the cookie jar
		$cookiejar = static::getInstance();

		// set the new value on the cookie
		$cookiejar->set($name, result($value));

		// set optional cookie settings
		if ($expiration and is_numeric($expiration))
		{
			// if it's < 31 days, assume it's an offset
			if ($expiration > 0 and $expiration <= 2678400)
			{
				$expiration = $expiration + time();
			}
			$cookiejar[$name]->setExpiration($expiration);
		}
		if ($path and is_string($path))
		{
			$cookiejar[$name]->setPath($path);
		}
		if ($domain and is_string($domain))
		{
			$cookiejar[$name]->setDomain($domain);
		}
		if (is_bool($secure))
		{
			$cookiejar[$name]->setSecure($secure);
		}
		if (is_bool($http_only))
		{
			$cookiejar[$name]->setHttp_Only($http_only);
		}
	}

	/**
	 * Deletes a cookie by making the value null and expiring it.
	 *
	 *     \Cookie::delete('theme');
	 *
	 * @param   string   cookie name
 	 * @param   string    path of the cookie
	 * @param   string    domain of the cookie
	 * @param   boolean   if true, the cookie should only be transmitted over a secure HTTPS connection
	 * @param   boolean   if true, the cookie will be made accessible only through the HTTP protocol
	 *
	 * @since 1.0.0
 	 */
	public static function delete($name, $path = null, $domain = null, $secure = null, $http_only = null)
	{
		// get the cookie jar
		$cookiejar = static::getInstance();

		// delete the cookie
		$cookiejar->delete($name);

		if ($path and is_string($path))
		{
			$cookiejar[$name]->setPath($path);
		}
		if ($domain and is_string($domain))
		{
			$cookiejar[$name]->setDomain($domain);
		}
		if (is_bool($secure))
		{
			$cookiejar[$name]->setSecure($secure);
		}
		if (is_bool($http_only))
		{
			$cookiejar[$name]->setHttp_Only($http_only);
		}
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
		if ($request = \Request::getInstance())
		{
			return $request->getInput()->getCookie();
		}

		// no active request, return the global instance
		return static::$instance;
	}
}
