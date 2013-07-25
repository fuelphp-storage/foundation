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
	 * Define a new application
	 *
	 * @param  $config  array with application configuration information
	 * @throws InvalidArgumentException if a required config value is missing or incorrect
	 * @returns	Application
	 *
	 * @since  2.0.0
	 */
	public static function define(array $config = array())
	{
		// application name and path
		if (isset($config['name']))
		{
			if (is_array($config['name']))
			{
				$config['path'] = reset($config['name']);
				$config['name'] = key($config['name']);
			}
			else
			{
				$config['path'] = APPSPATH.$config['name'];
			}
			$config['path'] = realpath($config['path']);

			if ( ! is_dir($config['path']))
			{
				throw new \InvalidArgumentException('The path "'.$config['path'].'" does not exist for application "'.$config['name'].'".');
			}

			// do we already have this application?
			if (isset(static::$applications[$config['name']]))
			{
				throw new \InvalidArgumentException('The application "'.$config['name'].'" is already defined.');
			}
		}
		else
		{
			throw new \InvalidArgumentException('The application name is missing from the configuration array.');
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

		return static::$applications[$config['name']] = \Dependency::resolve('application', array($config['name'], $config['path'], $config['namespace'], $config['environment']));
	}

	/**
	 * Get a defined application instance
	 *
	 * @param  $app  name of the application, or none for the first application defined
	 * @throws InvalidArgumentException if the requested application does not exist
	 * @returns	Application
	 *
	 * @since  2.0.0
	 */
	public static function with($app)
	{
		if ( ! isset(static::$applications[$app]))
		{
			throw new \InvalidArgumentException('There is no application defined named "'.$app.'".');
		}

		return static::$applications[$app];
	}

	/**
	 * Get the object instance for this Facade
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		return static::$instance;
	}
}
