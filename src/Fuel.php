<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation;

use Fuel\Config\Container as Config;
use Fuel\Dependency\Container as Dic;
use League\Container\Exception\ReflectionException;

/**
 * Fuel class
 *
 * @package  Fuel\Foundation
 *
 * @since  1.0.0
 */
class Fuel
{
	/**
	 * @var  string  The global version of framework
	 *
	 * @since  1.0.0
	 */
	const VERSION = '2.0-dev';

	/**
	 * Whether or not the framework is initialized
	 *
	 * @since  1.0.0
	 */
	protected static $initialized = false;

	/**
	 * This frameworks Dependency object
	 *
	 * @since  1.0.0
	 */
	protected static $dic = null;

	/**
	 * This frameworks Appliction object
	 *
	 * @since  1.0.0
	 */
	protected static $app = null;

	/**
	 * Initialize the framework
	 *
	 * @since  1.0.0
	 */
	protected static function initialize()
	{
		// some handy constants
		defined('DS') or define('DS', DIRECTORY_SEPARATOR);
		defined('CRLF') or define('CRLF', chr(13).chr(10));

		// do we have access to mbstring? We need this in order to work with UTF-8 strings
		defined('MBSTRING') or define('MBSTRING', function_exists('mb_get_info'));

		// get the Dependency Container instance
		$dic = static::getDic();

		// setup the autoloader if none was set yet
		try
		{
			$loader = $dic->get('autoloader');
		}
		catch (ReflectionException $e)
		{
			// fetch the composer autoloader instance
			$loader = require VENDORPATH.'autoload.php';

			// allow the framework to access the composer autoloader
			$dic->add('autoloader', $loader);
		}

		// setup the errorhandler if none was set yet
		try
		{
			$errorhandler = $dic->get('errorhandler');
		}
		catch (ReflectionException $e)
		{
			// setup the shutdown, error & exception handlers
			$errorhandler = new Error($dic);

			// setup the shutdown, error & exception handlers
			$dic->add('errorhandler', $errorhandler);
		}

		// get all defined namespaces
		$prefixes = array_merge($loader->getPrefixes(), $loader->getPrefixesPsr4());

		// determine the installation root if needed
		if ( ! defined('ROOTPATH'))
		{
			$path = reset($prefixes['Fuel\\Foundation\\']);
			if ($path = realpath(substr($path, 0, strpos($path, '/fuelphp/foundation')).DS.'..'.DS))
			{
				define('ROOTPATH', $path.DS);
			}
		}

		// scan all composer packages loaded for the presence of FuelServiceProviders
		foreach ($prefixes as $namespace => $paths)
		{
			// does this package define a service provider
			if (class_exists($class = trim($namespace,'\\').'\\Providers\\FuelServiceProvider'))
			{
				// register it with the DiC
				$dic->addServiceProvider(new $class);
			}
		}

		// TODO: needs to be changed to something more clever!
		// scan all composer packages loaded for the presence of FuelLibraryProviders
		foreach ($prefixes as $namespace => $paths)
		{
			// does this package define a service provider
			if (class_exists($class = trim($namespace,'\\').'\\Providers\\FuelLibraryProvider'))
			{
				// load the library provider
				$provider = new $class($dic, $namespace, $paths);

				// validate the provider
				if ( ! $provider instanceOf LibraryProvider)
				{
					throw new \RuntimeException('FOU-025: FuelBootstrap for ['.$namespace.'] must be an instance of \Fuel\Foundation\LibraryProvider');
				}

				// initialize the loaded library
				$provider->initialize();
			}
		}

		// mark we're initialized
		static::$initialized = true;
	}

	/**
	 * Create a new application instance, the main application component
	 * or return an already created one
	 *
	 * @param  string  name to identify this application
	 * @param  string  the namespace of the main application component
	 * @param  string  the environment this application component has to run in
	 * @return  Component  the created application object
	 *
	 * @since  2.0.0
	 */
	public static function forge($name, $appNamespace, $appEnvironment = 'development')
	{
		if ( ! static::$initialized)
		{
			static::initialize();
		}

		// get the Dependency Container instance
		$dic = static::getDic();

		try
		{
			// check if we already have an application by this name
			$app = $dic->multiton('application', $name);
			throw new \InvalidArgumentException('FOU-xxx: An application by the name of ['.$name.'] already exists.');
		}
		catch (ReflectionException $e)
		{
			// create the application instance
			$app = $dic->get('Fuel\Foundation\Application', [$name, $appNamespace, $appEnvironment]);

			// allow the framework to access the application object
			$dic->add('application::'.$name, $app);
		}

		// make the first one defined the main application
		try
		{
			// check if we already have an main application defined
			$dic->multiton('application', '__main');
		}
		catch (ReflectionException $e)
		{
			// create the main application as an alias of the created application
			$dic->add('application::__main', $app);
			static::$app = $app;
		}

		// return the created applications main component
		return $app->getRootComponent();
	}

	/**
	 * set the DiC
	 *
	 * @param   Fuel\Dependency\Container  $dic  This frameworks DiC instance
	 * @return   Fuel\Dependency\Container  This frameworks DiC instance
	 *
	 * @since  2.0.0
	 */
	public static function setDic($dic = null)
	{
		// if a custom DiC is passed, use that
		if ($dic and $dic instanceOf Dic)
		{
			static::$dic = $dic;
		}

		// else set one up if not done yet
		elseif ( ! static::$dic)
		{
			// get us a Dependency Container instance
			static::$dic = new Dic;

			// register the DiC on classname so it can be auto-resolved
			static::$dic->add('Fuel\Dependency\Container', $dic);

		}

		// register the dic for manual resolving
		static::$dic->add('dic', $dic);

		return static::$dic;
	}

	/**
	 * get the DiC
	 *
	 * @return   Fuel\Dependency\Container  This frameworks DiC instance
	 *
	 * @since  2.0.0
	 */
	public static function getDic()
	{
		return static::$dic ?: static::setDic();
	}

	/**
	 * get the application object
	 *
	 * @return   Application  This frameworks Application object
	 *
	 * @since  2.0.0
	 */
	public static function getApp()
	{
		return static::$app;
	}

}

