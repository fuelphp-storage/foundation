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
 * PackageProvider base class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class PackageProvider
{
	/**
	 * @var  string  base namespace of this package
	 */
	protected $namespace;

	/**
	 * @var  array  array of paths defined for this namespace
	 */
	protected $paths = array();

	/**
	 * Class constructor, initialize the package, check for existence of a
	 * bootstrap file, and if found, process it
	 *
	 * @param
	 *
	 * @since 2.0.0
	 */
	public function __construct($dic, $namespace, Array $paths = array())
	{
		// normalize the namespace and store it
		$this->namespace = trim($namespace, '\\').'\\';

		// check and normalize the paths, and store them
		foreach ($paths as $path)
		{
			if ($path = realpath($path))
			{
				$this->paths[] = $path.DS;
			}
		}

		// does this package define a service provider
		if (class_exists($class = $this->namespace.'Providers\\FuelServiceProvider'))
		{
			// register it with the DiC
			$dic->registerService(new $class);
		}
	}

	/**
	 * Package initialization method. This method is called as soon as the package
	 * is initially loaded, either by the framework bootstrap, or when you manually
	 * load a new package into the autoloader using the Package class.
	 *
	 * @since 2.0.0
	 */
	public function initPackage()
	{
	}

	/**
	 * Package enabler method. By default, a loaded package is disabled.
	 * When you instruct your application to use the package, this enabler gets
	 * called. You can use it to prep the application for use of the package.
	 *
	 * @param  Application  $app  The application instance that wants to enable this package
	 *
	 * @since 2.0.0
	 */
	public function enablePackage($app)
	{
	}

	/**
	 * Package disabler method. By default, a loaded package is disabled.
	 * When you instruct your application to unload a package, this disabler gets
	 * called. You can use it to cleanup any setup the package has made in the
	 * application that was using it.
	 *
	 * @param  Application  $app  The application instance that had enabled this package
	 *
	 * @since 2.0.0
	 */
	public function disablePackage($app)
	{
	}
}
