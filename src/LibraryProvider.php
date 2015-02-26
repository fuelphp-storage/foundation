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
 * LibraryProvider base class
 */
abstract class LibraryProvider
{
	/**
	 * @var Container
	 */
	protected $container;

	/**
	 * @var string
	 */
	protected $namespace;

	/**
	 * @var array
	 */
	protected $paths = [];

	/**
	 * Initialize the package, check for existence of a
	 * bootstrap file, and if found, process it
	 *
	 * @param Container $container
	 * @param string    $namespace
	 * @param array     $paths
	 */
	public function __construct(Container $container, $namespace, array $paths = [])
	{
		// store the DiC
		$this->container = $container;

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
	}

	/**
	 * Library's initialization method. This method is called as soon as the library
	 * is initially loaded, either by the framework bootstrap, or when you manually
	 * load a new library into the autoloader using the Application's getLibrary() method.
	 */
	public function initialize()
	{
	}

	/**
	 * Library enabler method
	 *
	 * When you instruct your application to use the library, this enabler gets
	 * called. You can use it to prep the application for use of the library.
	 * By default, a loaded library is disabled.
	 *
	 * @param Application $app
	 */
	public function enable(Application $app)
	{
	}

	/**
	 * Library disabler method
	 *
	 * When you instruct your application to unload a library, this disabler gets
	 * called. You can use it to cleanup any setup the library has made in the
	 * application that was using it.
	 *
	 * @param Application $app
	 */
	public function disable(Application $app)
	{
	}
}
