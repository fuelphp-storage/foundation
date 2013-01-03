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
 * Package Loader
 *
 * Default FuelPHP Package loader class.
 *
 * @package  FuelPHP\Foundation
 *
 * @since  2.0.0
 */
class Package
{
	/**
	 * @var  \FuelPHP\Foundation\Environment
	 */
	protected $env;

	/**
	 * @var  string  name of this loader
	 *
	 * @since  2.0.0
	 */
	public $name;

	/**
	 * @var  string  basepath for the package
	 *
	 * @since  2.0.0
	 */
	protected $path = '';

	/**
	 * @var  bool  whether this package is routable
	 *
	 * @since  2.0.0
	 */
	protected $routable = false;

	/**
	 * @var  string  base namespace for the package (with trailing backslash when not empty)
	 *
	 * @since  2.0.0
	 */
	protected $namespace = '';

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
	 * Set a base path for the package
	 *
	 * @param   string  $path
	 * @return  Package
	 *
	 * @since  2.0.0
	 */
	public function setPath($path)
	{
		$this->path = rtrim($path, '/\\').'/';
		return $this;
	}

	/**
	 * Returns the base path for this package
	 *
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * Sets routability of this package
	 *
	 * @param   bool  $routable
	 * @return  Package
	 *
	 * @since  2.0.0
	 */
	public function setRoutable($routable)
	{
		$this->routable = (bool) $routable;
		return $this;
	}

	/**
	 * Assigns a name to this package
	 *
	 * @param   string  $name
	 * @return  Package
	 *
	 * @since  2.0.0
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * Set a base namespace for the package, only classes from that namespace are loaded
	 *
	 * @param   string  $namespace
	 * @return  Package
	 *
	 * @since  2.0.0
	 */
	public function setNamespace($namespace)
	{
		$this->namespace = $namespace ? trim($namespace, '\\').'\\' : $namespace;
		return $this;
	}

}
