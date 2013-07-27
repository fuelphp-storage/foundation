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
     * @param array|string  $paths  The location(s) of the classes
     * @param string       $prefix  The classes prefix
     * @param bool         $position  True = prepend, False = append, Null, do nothing
	 *
	 * @since  2.0.0
	 */
	public static function forge($paths, $prefix, $position = false)
	{
		// load up the installed composer libraries, and check for a Fuel bootstrap
		// this is done in a closure to create a clean environment to include the file in
		$bootstrap = function($file) {
			return include $file;
		};

		is_array($paths) or $paths = array($paths);

		foreach ($paths as $srcPath)
		{
			// determine the package name from the path
			$package = explode('/', $srcPath);
			if (array_pop($package) !== 'src')
			{
				continue;
			}
			$package = array(array_pop($package), array_pop($package));
			$package = implode('/', array_reverse($package));

			isset(static::$packages[$package]) or static::$packages[$package] = array();

			if ($position !== null)
			{
				\Composer::add($prefix, $srcPath, $position);
			}

			if (file_exists($srcPath.DS.'bootstrap.php'))
			{
				if (($postinit = $bootstrap($srcPath.DS.'bootstrap.php')) instanceOf \Closure)
				{
					static::$packages[$package][] = $postinit;
				}
			}
		}
	}

	/**
	 * Initialization, load up all composer installed packages, and run any bootstraps found
	 *
	 * @since  2.0.0
	 */
	public static function initialize()
	{
		foreach (\Composer::getPrefixes() as $ns => $srcPaths)
		{
			static::forge($srcPaths, $ns, null);
		}
	}

	/**
	 * This facade doesn't have instances
	 *
	 * @since  2.0.0
	 */
	protected static function getInstance()
	{
		return null;
	}
}
