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
		// make sure the required fields exist
		$config = array_merge(array('path' => null, 'namespace' => '', 'environment' => ''), $config);

		// create and return this application
		return \Dependency::multiton('application', $name, array($name, $config['path'], $config['namespace'], $config['environment']));
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
	 * Get the object instance for this Facade
	 *
	 * @return  Application
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		$stack = \Dependency::resolve('requeststack');
		if ($request = $stack->top())
		{
			$app = $request->getApplication();
		}
		else
		{
			$app = $this->container->resolve('application.main');
		}

		return $app;
	}
}
