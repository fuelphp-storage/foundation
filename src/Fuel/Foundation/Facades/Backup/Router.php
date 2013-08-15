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
 * Router Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  1.0.0
 */
class Router extends Base
{
	/**
	 * Forge a new router object
	 *
	 * @param  Application  $app  Application object on which to forge this security object
	 *
	 * @returns	Fuel/Routing/Router
	 *
	 * @since  2.0.0
	 */
	public static function forge(AppInstance $app)
	{
		// do we already have this instance?
		$name = $app->getName();
		if (\Dependency::isInstance('router', $name))
		{
			throw new \RuntimeException('The router object "'.$name.'" is already forged.');
		}

		return \Dependency::multiton('router', $name);
	}

	/**
	 * Get the object instance for this Facade
	 *
	 * @returns	Input
	 *
	 * @since  2.0.0
	 */
	public static function translate($uri, $method)
	{
		// resolve the route
		$route = static::getInstance()->translate($uri, $method);

		// find a match
		foreach (\Application::getInstance()->getNamespaces() as $namespace)
		{
			// skip non-routeable namespaces
			if ( ! $namespace['routeable'] and \Request::isMainRequest())
			{
				continue;
			}

			// skip if we don't have a prefix match
			if ($namespace['prefix'] and strpos($route->translation, $namespace['prefix']) !== 0)
			{
				continue;
			}

			$route->setNamespace($namespace['namespace']);

			// get the segments from the translated route
			$segments = explode('/', ltrim(substr($route->translation, strlen($namespace['prefix'])),'/'));

			$arguments = array();
			while(count($segments))
			{
				$class = $route->namespace.'Controller\\'.implode('\\', array_map('ucfirst', $segments));

				if ( ! class_exists($class, false))
				{
					$file = $namespace['path'].'classes'.DS.'Controller'.DS.implode('/', array_map('ucfirst', $segments)).'.php';
					if (file_exists($file))
					{
						include $file;
					}
				}

				if (class_exists($class))
				{
					$route->path = $namespace['path'];
					$route->controller = $class;
					break;
				}
				array_unshift($arguments, array_pop($segments));
			}

			// did we find a match
			if ($route->controller)
			{
				// then stop looking
				break;
			}
		}

		// any segments left?
		if ( ! empty($segments))
		{
			$route->action = ucfirst(array_shift($arguments));
		}

		// more? return them as additional segments
		$route->segments = empty($arguments) ? array() : $arguments;

		return $route;
	}

	/**
	 * Get the object instance for this Facade
	 *
	 * @returns	Fuel/Routing/Router
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		// get the current request instance
		if ($app = \Application::getInstance())
		{
			return \Dependency::multiton('router', $app->getName());
		}

		// no active application, so no instance available
		return null;
	}
}
