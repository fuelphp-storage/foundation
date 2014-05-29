<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation\Providers;

use Fuel\Foundation\LibraryProvider;

/**
 * FuelPHP LibraryProvider class for this composer library
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class FuelLibraryProvider extends LibraryProvider
{
	/**
	 * Library initialization method. This method is called as soon as the library
	 * is initially loaded, either by the framework bootstrap, or when you manually
	 * load a new library into the autoloader using the applications getLibrary() method.
	 *
	 * @since 2.0.0
	 */
	public function initialize()
	{
		// fetch the alias instance
		$alias = $this->dic->resolve('alias');

		/**
		 * Alias the Fuel class to global
		 */
		$alias->alias('Fuel', 'Fuel\Foundation\Fuel');

		/**
		 * Alias all Foundation facade classes to global
		 */
		$alias->aliasNamespace('Fuel\Foundation\Facades', '');

		/**
		 * Alias the base controllers to the Fuel\Controller namespace
		 */
		$alias->aliasNamespace('Fuel\Foundation\Controller', 'Fuel\Controller');
	}

	/**
	 * Library enabler method. By default, a loaded library is disabled.
	 * When you instruct your application to use the library, this enabler gets
	 * called. You can use it to prep the application for use of the library.
	 *
	 * @param  Application  $app  The application instance that wants to enable this library
	 *
	 * @since 2.0.0
	 */
	public function enable($app)
	{
	}

	/**
	 * Library disabler method. By default, a loaded library is disabled.
	 * When you instruct your application to unload a library, this disabler gets
	 * called. You can use it to cleanup any setup the library has made in the
	 * application that was using it.
	 *
	 * @param  Application  $app  The application instance that had enabled this library
	 *
	 * @since 2.0.0
	 */
	public function disable($app)
	{
	}
}
