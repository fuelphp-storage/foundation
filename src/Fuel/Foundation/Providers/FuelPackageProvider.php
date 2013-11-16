<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation\Providers;

use Fuel\Foundation\PackageProvider;

/**
 * FuelPHP PackageProvider class for this package
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class FuelPackageProvider extends PackageProvider
{
	/**
	 * Package initialization method. This method is called as soon as the package
	 * is initially loaded, either by the framework bootstrap, or when you manually
	 * load a new package into the autoloader using the Package class.
	 *
	 * @since 2.0.0
	 */
	public function initPackage()
	{
		/**
		 * Alias the base controllers to the Fuel\Controller namespace
		 */
		$this->dic->resolve('alias')->aliasNamespace('Fuel\Foundation\Controller', 'Fuel\Controller');
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
