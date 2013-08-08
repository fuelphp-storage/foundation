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
	 * @var Application main application instance
	 */
	protected static $mainApp;

	/**
	 * Forge a new application
	 *
	 * @param  $name  name of the application
	 * @param  $config  array with application configuration information
	 *
	 * @throws InvalidArgumentException if a required config value is missing or incorrect
	 * @throws RuntimeException if the application to forge already exists
	 *
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
		if (\Dependency::isInstance('application', $name))
		{
			throw new \RuntimeException('The application "'.$name.'" is already forged.');
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
		\Composer::getInstance()->add($config['namespace'], $config['path'].DS.'classes', true);

		// register this application
		$app = \Dependency::multiton('application', $name, array($name, $config['path'], $config['namespace'], $config['environment']));

		// if this is the first one forged, store it as the main Application
		if (static::$mainApp === null)
		{
			static::$mainApp = $app;
		}
		return $app;
	}

	/**
	 * Get a defined application instance
	 *
	 * @param  $name  name of the application
	 *
	 * @throws RuntimeException if the application to get does not exist
	 *
	 * @returns	Application
	 *
	 * @since  2.0.0
	 */
	public static function get($name)
	{
		if ( ! \Dependency::isInstance('application', $name))
		{
			throw new \RuntimeException('There is no application defined named "'.$name.'".');
		}

		return \Dependency::multiton('application', $name);
	}

	/**
	 * Returns current active Application
	 *
	 * @return  Application
	 *
	 * @since  2.0.0
	 */
	public static function getActive()
	{
		return static::getInstance();
	}

	/**
	 * Get the object instance for this Facade
	 *
	 * @return  Application
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		if ($request = \Request::getActive())
		{
			return $request->getApplication();
		}

		return static::$mainApp;
	}
}
