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
 * Application Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Application extends Base
{
	/**
	 * @var  array  List of loaded applications
	 *
	 * @since  2.0.0
	 */
	protected static $applications = array();

	/**
	 * Forge a new application
	 *
	 * @param  $name  name of the application
	 * @param  $config  array with application configuration information
	 * @throws InvalidArgumentException if a required config value is missing or incorrect
	 * @returns	Application
	 *
	 * @since  2.0.0
	 */
	public static function forge($name, array $config = array())
	{
		// application path
		if ( ! isset($config['path']))
		{
			$config['path'] = realpath(APPSPATH.$name);
		}
		if ( ! is_dir($config['path']))
		{
			throw new \InvalidArgumentException('The path "'.$config['path'].'" does not exist for application "'.$name.'".');
		}

		// do we already have this application?
		if (isset(static::$applications[$name]))
		{
			throw new \InvalidArgumentException('The application "'.$name.'" is already forged.');
		}

		// application namespace, defaults to global
		if (empty($config['namespace']))
		{
			$config['namespace'] = '';
		}

		// application environment, defaults to 'development'
		if (empty($config['environment']))
		{
			$config['environment'] = 'development';
		}


		// add the root namespace for this application to composer
		\Composer::getLoader()->add($config['namespace'], $config['path'].DS.'classes', true);

		return static::$applications[$name] = \Dependency::resolve('application', array($name, $config['path'], $config['namespace'], $config['environment']));
	}

	/**
	 * Get a defined application instance
	 *
	 * @param  $name  name of the application
	 * @throws InvalidArgumentException if the requested application does not exist
	 * @returns	Application
	 *
	 * @since  2.0.0
	 */
	public static function get($name)
	{
		if ( ! isset(static::$applications[$name]))
		{
			throw new \InvalidArgumentException('There is no application defined named "'.$name.'".');
		}

		return static::$applications[$name];
	}

	/**
	 * Get the object instance for this Facade
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		return null;
	}
}
