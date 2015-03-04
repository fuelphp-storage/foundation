<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2015 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation;

use Fuel\Dependency\Container;

/**
 * Fuel bootstrapping class
 */
class Fuel
{
	/**
	 * @var string
	 */
	const VERSION = '2.0-dev';

	/**
	 * Whether or not the framework is initialized
	 */
	protected static $initialized = false;

	/**
	 * Dependency container
	 */
	protected static $container;

	/**
	 * Appliction object
	 */
	protected static $app;

	/**
	 * Initialize the framework
	 */
	protected static function initialize()
	{
		// some handy constants
		defined('DS') or define('DS', DIRECTORY_SEPARATOR);
		defined('CRLF') or define('CRLF', chr(13).chr(10));

		// do we have access to mbstring? We need this in order to work with UTF-8 strings
		defined('MBSTRING') or define('MBSTRING', function_exists('mb_get_info'));

		// get the Dependency Container instance
		$container = static::getContainer();

		// setup the autoloader if none was set yet
		if ( ! $container->isInstance('autoloader'))
		{
			// fetch the composer autoloader instance
			$loader = require VENDORPATH.'autoload.php';

			// allow the framework to access the composer autoloader
			$container->add('autoloader', $loader);
		}

		// setup the errorhandler if none was set yet
		if ( ! $container->isInstance('errorhandler'))
		{
			$container->add('errorhandler', new Error());
		}

		$autoloader = $container->get('autoloader');

		// get all defined namespaces
		$prefixes = array_merge($autoloader->getPrefixes(), $autoloader->getPrefixesPsr4());

		// determine the installation root if needed
		// TODO: find a better way to do that
		if ( ! defined('ROOTPATH'))
		{
			$path = reset($prefixes['Fuel\\Foundation\\']);

			if ($path = realpath(substr($path, 0, strpos($path, '/fuelphp/foundation')).DS.'..'.DS))
			{
				define('ROOTPATH', $path.DS);
			}
		}

		$fuelNamespaces = array_filter(array_keys($prefixes), function($key)
		{
			return substr($key, 0, 5) === 'Fuel\\';
		});

		$nonFuelNamespaces = array_diff(array_keys($prefixes), $fuelNamespaces);

		// scan all fuel packages loaded for the presence of FuelServiceProviders
		static::loadServiceProviders($fuelNamespaces);

		// scan the rest of composer packages loaded for the presence of FuelServiceProviders
		static::loadServiceProviders($nonFuelNamespaces);

		// scan all fuel packages loaded for the presence of FuelLibraryProviders
		static::loadLibraryProviders(array_intersect_key($prefixes, array_flip($fuelNamespaces)));

		// scan the rest of composer packages loaded for the presence of FuelLibraryProviders
		static::loadLibraryProviders(array_intersect_key($prefixes, array_flip($nonFuelNamespaces)));

		// mark we're initialized
		static::$initialized = true;
	}

	/**
	 * Scans a set of namespaces for service providers and loads them
	 *
	 * @param array $namespaces
	 */
	protected static function loadServiceProviders(array $namespaces)
	{
		foreach ($namespaces as $namespace)
		{
			// does this package define a service provider
			if (class_exists($class = trim($namespace,'\\').'\\Providers\\FuelServiceProvider'))
			{
				// register it with the Container
				static::getContainer()->addServiceProvider($class);
			}
		}
	}

	/**
	 * Scans a set of namespaces for library providers and loads them
	 *
	 * @param array $prefixes
	 */
	protected static function loadLibraryProviders(array $prefixes)
	{
		foreach ($prefixes as $namespace => $paths)
		{
			// does this package define a library provider
			if (class_exists($class = trim($namespace,'\\').'\\Providers\\FuelLibraryProvider'))
			{
				// load the library provider
				$provider = new $class(static::$container, $namespace, $paths);

				// validate the provider
				if ( ! $provider instanceOf LibraryProvider)
				{
					throw new \RuntimeException('FOU-025: FuelBootstrap for ['.$namespace.'] must be an instance of \Fuel\Foundation\LibraryProvider');
				}

				// initialize the loaded library
				$provider->initialize();
			}
		}
	}

	/**
	 * Creates a new application instance, the main application component
	 * or return an already created one
	 *
	 * @param string $name
	 * @param string $appNamespace
	 * @param string $appEnvironment
	 *
	 * @return Component
	 */
	public static function forge($name, $appNamespace, $appEnvironment = 'development')
	{
		if ( ! static::$initialized)
		{
			static::initialize();
		}

		// get the Dependency Container instance
		$container = static::getContainer();

		// check if we already have an application by this name
		if ($container->isInstance('application', $name))
		{
			throw new \InvalidArgumentException(sprintf('FOU-xxx: An application by the name of [%s] already exists.', $name));
		}

		// create the application instance
		$app = $container->multiton('application', $name, [$name, $appNamespace, $appEnvironment]);

		// make the first one defined the main application
		if ( ! $container->isInstance('application', '__main'))
		{
			// create the main application as an alias of the created application
			$container->add('application::__main', $app);
			static::$app = $app;
		}

		$app->initialize();

		// return the created applications main component
		return $app->getRootComponent();
	}

	/**
	 * Returns the Container
	 *
	 * @return Container
	 */
	public static function getContainer()
	{
		if ( ! isset(static::$container))
		{
			static::setContainer();
		}

		return static::$container;
	}

	/**
	 * Sets the Container
	 *
	 * @param Container $container
	 */
	public static function setContainer(Container $container = null)
	{
		if ( ! isset($container))
		{
			// get us a Dependency Container instance
			$container = new Container;

			// register the Container on classname so it can be auto-resolved
			$container->add('Fuel\Dependency\Container', $container);
		}

		// register the dic for manual resolving
		$container->add('dic', $container);
		$container->add('container', $container);

		static::$container = $container;
	}

	/**
	 * Returns the main application object
	 *
	 * @return Application
	 */
	public static function getApp()
	{
		return static::$app;
	}
}
