<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    FuelPHP\Foundation
 * @version    2.0
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 */

namespace FuelPHP\Foundation;

/**
 * Application Base class
 *
 * Wraps an application package into an object to work with.
 *
 * @package  FuelPHP\Foundation
 *
 * @since  2.0.0
 */
class Application
{
	/**
	 * @var  Environment
	 *
	 * @since  2.0.0
	 */
	protected $env;

	/**
	 * @var  FuelPHP\Foundation\Loader  $loader  application components loader
	 *
	 * @since  2.0.0
	 */
	protected $loader = null;

	/**
	 * Constructor
	 *
	 * @since  2.0.0
	 */
	public function __construct($appName, $appPath)
	{
		// set the environment variable necessary for the package loader object
		$this->env = \FuelPHP\Foundation\Environment::singleton();

		// setup a component loader
		$this->loader = $this->env->forge('FuelPHP\Foundation\Loader');

		// load the application package
		$this->loader->loadPackage(array($appName, $appPath), Loader::TYPE_APPLICATION);
	}
}
