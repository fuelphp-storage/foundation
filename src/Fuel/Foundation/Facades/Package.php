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
 * Package Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Package extends Base
{
	/**
	 * @var  array  List of installed packages and their application init closure(s)
	 *
	 * @since  2.0.0
	 */
	protected static $packages = array();

	/**
	 * Forge a new Package at runtime
	 *
     * @param string        $prefix    The classes namespace prefix
     * @param array|string  $paths     The location(s) of the classes in the package
     * @param bool          $position  true = prepend, false = append, null, do nothing
	 *
	 * @since  2.0.0
	 */
	public static function forge($prefix, $paths, $position = false)
	{
		// make sure paths is an array
		is_array($paths) or $paths = array($paths);

		// get the packages container
		$packages = static::$dic->resolve('packages');

		// check if this package has a PackageProvider for us
		if (class_exists($class = trim($prefix, '\\').'\\Providers\\FuelPackageProvider'))
		{
			// load the package provider
			$provider = new $class($prefix, $paths);
		}
		else
		{
			// create a base provider instance
			$provider = $dic->resolve('packageprovider', array($prefix, $paths));
		}

		// validate the provider
		if ( ! $provider instanceOf PackageProvider)
		{
			throw new RuntimeException('PackageProvider for '.$prefix.' must be an instance of \Fuel\Foundation\PackageProvider');
		}

		// initialize the loaded package
		$provider->initPackage();

		// and store it in the container
		$packages->set($prefix, $provider);

		// let the autoloader know we have a new package
		if ($position !== null)
		{
			\Autoloader::add($prefix, $paths, $position);
		}
	}

	/**
	 * Enable a package for the current application
	 *
     * @param  string  $prefix  The classes namespace prefix
     *
     * @return  mixed  Return value of the package enable method, or null
	 *
	 * @since  2.0.0
	 */
	public static function enable($prefix)
	{
		// get the packages container
		$packages = static::$dic->resolve('packages');

		if ($provider = $packages->get($prefix, null))
		{
			return $provider->enablePackage(\Application::getInstance());
		}
	}

	/**
	 * Disable a package for the current application
	 *
     * @param  string  $prefix  The classes namespace prefix
     *
     * @return  mixed  Return value of the package disable method, or null
	 *
	 * @since  2.0.0
	 */
	public static function disable($prefix)
	{
		// get the packages container
		$packages = static::$dic->resolve('packages');

		if ($provider = $packages->get($prefix, null))
		{
			return $provider->disablePackage(\Application::getInstance());
		}
	}

	/**
	 * This facade doesn't provide instances
	 *
	 * @since  2.0.0
	 */
	protected static function getInstance()
	{
		return null;
	}
}
