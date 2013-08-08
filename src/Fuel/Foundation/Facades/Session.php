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
	 * array of global config defaults
	 */
	protected static $_defaults = array(
		'driver'                    => 'native',
		'match_ip'                  => false,
		'match_ua'                  => true,
		'cookie_domain'             => '',
		'cookie_path'               => '/',
		'cookie_http_only'          => null,
		'encrypt_cookie'            => true,
		'expire_on_close'           => false,
		'expiration_time'           => 7200,
		'rotation_time'             => 300,
		'flash_id'                  => 'flash',
		'flash_auto_expire'         => true,
		'flash_expire_after_get'    => true,
		'post_cookie_name'          => ''
	);

	/**
	 * Produces fully configured session driver instances
	 *
	 * @param	array|string	full driver config or just driver type
	 */
	public static function forge($custom = array())
	{
		$config = \Config::get('session', array());

		// When a string was passed it's just the driver type
		if ( ! empty($custom) and ! is_array($custom))
		{
			$custom = array('driver' => $custom);
		}

		$config = array_merge(static::$_defaults, $config, $custom);

		if (empty($config['driver']))
		{
			throw new \RuntimeException('No session driver given or no default session driver set.');
		}

		// determine the driver to load
		if ($config['driver'] instanceOf \Fuel\Session\Driver)
		{
			$driver = $config['driver'];
		}
		elseif (class_exists($config['driver']))
		{
			$class = $config['driver'];
			$driver = new $class($config);
		}
		else
		{
			$driver = \Dependency::resolve('session.'.$config['driver'], array($config));
		}

		// create the session manager
		$manager = \Dependency::resolve('session', array($driver, $config));

		// if a default session was forged
		if (empty($custom) and $app == \Application::getInstance())
		{
			if ($session = $app->getSession())
			{
				// reuse the one attached to the application
				return $session;
			}

			// assign the new one to the application
			$app->setSession($manager);
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
