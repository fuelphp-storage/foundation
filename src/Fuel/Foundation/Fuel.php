<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation;

/**
 * Fuel class
 *
 * The Fuel class bootstraps the framework, and provides a static entry into the
 * framework, allowing easy access to commonly used components
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
	 * @var  Whoops\Run
	 *
	 * @since  2.0.0
	 */
	protected static $errorHandler;

	/**
	 * @var  Composer\Autoload\ClassLoader
	 *
	 * @since  2.0.0
	 */
	protected static $loader;

	/**
	 * @var  Fuel\Dependency\Container
	 *
	 * @since  2.0.0
	 */
	protected static $dic;

	/**
	 * @var  Fuel\Alias\Manager
	 *
	 * @since  2.0.0
	 */
	protected static $alias;

	/**
	 * @var  Fuel\Foundation\Input global input
	 *
	 * @since  2.0.0
	 */
	protected static $input;

	/**
	 * @var  Fuel\Config\Container global configuration
	 *
	 * @since  2.0.0
	 */
	protected static $config;

	/**
	 * @var  string  base url
	 *
	 * @since  2.0.0
	 */
	protected static $baseUrl = null;

	/**
	 * @var  array  List of loaded applications
	 *
	 * @since  2.0.0
	 */
	protected static $applications = array();

	/**
	 * @var bool  Flag to indicate we've initialized the framework
	 *
	 * @since  1.0.0
	 */
	protected static $initialized;

	/**
	 * Initialize the class
	 *
	 * @since  2.0.0
	 */
	public static function init()
	{
		// make sure we run only once
		if ( ! static::$initialized)
		{
			// setup the shutdown, error & exception handlers
			if (static::$errorHandler === null)
			{
				static::$errorHandler = new \Whoops\Run;
				static::$errorHandler->pushHandler(new \Whoops\Handler\PrettyPageHandler);

				static::$errorHandler->register();
			}

			// setup the DiC if we don't have one yet
			if (static::$dic === null)
			{
				static::setDic(new \Fuel\Dependency\Container);
			}

			// setup the alias manager if we don't have one yet
			if (static::$alias === null)
			{
				static::$alias = static::$dic->resolve('Fuel\Alias\Manager')->register();
			}

			// alias the Foundation classes to global to allow extension
			static::aliasNamespace('Fuel\Foundation', '');

			// initialize the global input container
			static::$input = static::$dic->resolve('Input', array(null));
			static::$input->fromGlobals();

			// initialize the global config container
			static::$config = static::$dic->resolve('Fuel\Config\Container');

			// load the global default config
			static::$config->addPath(APPSPATH);
			static::$config->load('config', null);

			// run the applications bootstrap if present
			if (file_exists(APPSPATH.'bootstrap.php'))
			{
				include APPSPATH.'bootstrap.php';
			}

			// mark the initialisation complete
			static::$initialized = true;
		}
		else
		{
			throw new \RuntimeException("You can't initialize the Fuel framework more than once.");
		}
	}

	/**
	 * Setup the framework application environment
	 *
	 * @param  $config  array with application configuration information
	 * @since  2.0.0
	 */
	public static function setApp($app, $namespace, $env)
	{
		// create application object
		if (is_array($app))
		{
			$path = reset($app);
			$app = key($app);
		}
		else
		{
			$path = APPSPATH.$app;
		}

		// add the root namespace for this application to composer
		static::$loader->add($namespace, $path.DS.'classes', true);

		return static::$applications[$app] = static::$dic->resolve('Application', array($app, $path, $namespace, $env));
	}

	/**
	 * Get an application object
	 *
	 * @param  $app  name of the application, or none for the first application defined
	 * @since  2.0.0
	 */
	public static function getApp($app = null)
	{
		if (func_num_args() == 0)
		{
			return reset(static::$applications);
		}

		if ( ! isset(static::$applications[$app]))
		{
			throw new \InvalidArgumentException('There is no application defined named "'.$app.'".');
		}

		return static::$applications[$app];
	}

	/**
	 * Set the Composer autoloader instance
	 *
	 * @since  2.0.0
	 */
	public static function setLoader($autoloader)
	{
		$autoloader instanceOf \Composer\Autoload\ClassLoader and static::$loader = $autoloader;
	}

	/**
	 * Get the global DiC
	 *
	 * @since  2.0.0
	 */
	public static function getLoader()
	{
		return static::$loader;
	}

	/**
	 * Set the global DiC
	 *
	 * @since  2.0.0
	 */
	public static function setDic($dic)
	{
		$dic instanceOf \Fuel\Dependency\Container and static::$dic = $dic;
	}

	/**
	 * Get the global DiC
	 *
	 * @since  2.0.0
	 */
	public static function getDic()
	{
		return static::$dic;
	}

	/**
	 * Get the global input object
	 *
	 * @since  2.0.0
	 */
	public static function getInput()
	{
		return static::$input;
	}

	/**
	 * Get the global config object
	 *
	 * @since  2.0.0
	 */
	public static function getConfig()
	{
		return static::$config;
	}

	/**
	 * Facade for Dic::resolve()
	 *
	 * @since  2.0.0
	 */
	public static function resolve($args)
	{
		return call_user_func_array(array(static::$dic, 'resolve'), func_get_args());
	}

	/**
	 * Facade for Dic::register()
	 *
	 * @since  2.0.0
	 */
	public static function register($args)
	{
		return call_user_func_array(array(static::$dic, 'register'), func_get_args());
	}

	/**
	 * Facade for Alias::alias()
	 *
	 * @since  2.0.0
	 */
	public static function alias($args)
	{
		return call_user_func_array(array(static::$alias, 'alias'), func_get_args());
	}

		/**
	 * Facade for Alias::aliasNamespace()
	 *
	 * @since  2.0.0
	 */
	public static function aliasNamespace($args)
	{
		return call_user_func_array(array(static::$alias, 'aliasNamespace'), func_get_args());
	}

	// -------------------------------------------------------------------------

	/**
	 * Constructor, to prevent instantiation of this class
	 *
	 * @since  2.0.0
	 */
	final private function __construct() { }
}
