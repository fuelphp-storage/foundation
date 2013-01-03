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
 * Loader
 *
 * This acts as a generic loader, handles Package loading.
 *
 * @package  FuelPHP\Foundation
 *
 * @since  2.0.0
 */
class Loader
{
	/**
	 * @var  int  keyname for Application packages
	 *
	 * @since  2.0.0
	 */
	const TYPE_APPLICATION = 0;

	/**
	 * @var  int  keyname for normal packages
	 *
	 * @since  2.0.0
	 */
	const TYPE_PACKAGE = 1000;

	/**
	 * @var  int  keyname for libraries (non routable, always last)
	 *
	 * @since  2.0.0
	 */
	const TYPE_LIBRARY = 100000;

	/**
	 * @var  Environment
	 *
	 * @since  2.0.0
	 */
	protected $env;

	/**
	 * @var  array  active loaders in a prioritized list
	 *
	 * @since  2.0.0
	 */
	protected $packages = array(
		Loader::TYPE_APPLICATION  => array(),
		Loader::TYPE_PACKAGE      => array(),
		Loader::TYPE_LIBRARY      => array(),
	);

	/**
	 * Constructor
	 *
	 * @since  2.0.0
	 */
	public function __construct()
	{
		// Set the environment variable necessary for the package loader object
		$this->env = \FuelPHP\Foundation\Environment::singleton();
	}

	/**
	 * Adds a package
	 *
	 * @param   string|Loader       $name
	 * @param   int                 $type
	 *
	 * @throws  \RuntimeException
	 *
	 * @return  Loader              for method chaining
	 *
	 * @since  2.0.0
	 */
	public function loadPackage($name, $type = Loader::TYPE_PACKAGE)
	{
		// return directly when already loaded
		if ($this->packageExists($name, $type))
		{
			return $this->getPackage($name, $type);
		}

		// directly add an unnamed package
		if ($name instanceof Package)
		{
			$package = $name;
			$name = uniqid();
		}
		// directly add a named package: array($name, $package)
		elseif (is_array($name) and end($name) instanceof Package)
		{
			$package = end($name);
			$name = reset($name);
		}
		// add a package using a name, or using array($name, $fullpath)
		else
		{
			! is_array($name) and $name = array($name, $this->env->getPath('Application').$name.'/');
			list($name, $path) = $name;

			// check if the package hasn't already been loaded
			if (isset($this->packages[$type][$name]))
			{
				throw new \RuntimeException('Package already loaded, can\'t be loaded twice.');
			}

			// fetch the Package loader
			$path = rtrim($path, '\/').'/';
			$package = require $path.'loader.php';
			if ( ! $package instanceof Package)
			{
				throw new \RuntimeException('Package loader must return an instance of FuelPHP\\Foundation\\Package');
			}
		}

		// register the path with the environment
		$this->env->addPath($name, $package->getPath(), true);

		// and mark the Package as loaded
		$this->packages[$type] = array($name => $package->setName($name)) + $this->packages[$type];

		return $package;
	}

	/**
	 * Check if a package is loaded already
	 *
	 * @param   string|array|Loader     $name
	 * @param   int                     $type
	 *
	 * @return  bool
	 */
	public function packageExists($name, $type = Loader::TYPE_PACKAGE)
	{
		// Ensure the name is a string
		is_string($name) or $name = is_array($name) ? reset($name) : $name->name;

		return isset($this->packages[$type][$name]);
	}

	/**
	 * Fetch a specific package
	 *
	 * @param   string  $name
	 * @param   int     $type
	 *
	 * @throws  \OutOfBoundsException
	 *
	 * @return  Loader
	 *
	 * @since  2.0.0
	 */
	public function getPackage($name, $type = Loader::TYPE_PACKAGE)
	{
		if ( ! $this->packageExists($name, $type))
		{
			throw new \OutOfBoundsException('Unknown package: '.$name);
		}

		return $this->packages[$type][$name];
	}

	/**
	 * Fetch all packages or just those of a specific type
	 *
	 * @param   int|null  $type  null for all, int for a specific type
	 *
	 * @throws  \OutOfBoundsException
	 *
	 * @return  array
	 *
	 * @since  2.0.0
	 */
	public function getPackages($type = null)
	{
		if (is_null($type))
		{
			return $this->packages;
		}
		elseif ( ! isset($this->packages[$type]))
		{
			throw new \OutOfBoundsException('Unknown package type: '.$type);
		}

		return $this->packages[$type];
	}
}
