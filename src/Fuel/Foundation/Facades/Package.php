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
	 * Load up all packages, and run any bootstraps found
	 *
	 * @since  2.0.0
	 */
	public static function bootstrap()
	{
		// load up the installed composer libraries, and check for a Fuel bootstrap
		$bootstrap = function($file) {
			return include $file;
		};

		foreach (\Composer::getLoader()->getPrefixes() as $ns => $srcpaths)
		{
			static::$packages[$ns] = array();
			foreach ($srcpaths as $srcpath)
			{
				if (file_exists($srcpath.DS.'bootstrap.php'))
				{
					if (($postinit = $bootstrap($srcpath.DS.'bootstrap.php')) instanceOf \Closure)
					{
						static::$packages[$ns][] = $postinit;
					}
				}
			}
		}
	}

	/**
	 * Get the object instance for this Facade
	 *
	 * @since  2.0.0
	 */
	protected static function getInstance()
	{
		return null;
	}
}
