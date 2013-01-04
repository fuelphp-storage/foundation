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
	 * @var  \FuelPHP\Foundation\Application
	 */
	protected $app;
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
	protected $path = null;

	/**
	 * @var  array  any package dependencies this package requires
	 *
	 * @since  2.0.0
	 */
	protected $packages = array();

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
	 * @var  array  package modules with array(relative path => relative subnamespace) (with trailing backslash)
	 *
	 * @since  2.0.0
	 */
	protected $modules = array();

	/**
	 * @var  array  registered classes, without the base namespace
	 *
	 * @since  2.0.0
	 */
	protected $classes = array();

	/**
	 * @var  string  a first segment required for find_file() & find_class() that will be stripped
	 *
	 * @since  2.0.0
	 */
	protected $findTrigger;

	/**
	 * @var  bool  whether the class's path is relative to the main namespace or fully PSR-0 compliant
	 *
	 * @since  2.0.0
	 */
	protected $relativeClassLoad = false;

	/**
	 * @var  string  string to prefix the Controller classname with, will be relative to the base namespace
	 *
	 * @since  2.0.0
	 */
	protected $classPrefixes = array(
		'application'  => 'Application\\',
		'controller'   => 'Controller\\',
		'model'        => 'Model\\',
		'presenter'    => 'Presenter\\',
		'task'         => 'Task\\',
	);

	/**
	 * Constructor
	 *
	 * @since  2.0.0
	 */
	public function __construct()
	{
		// set the environment variable necessary for the package loader object
		$this->env = \FuelPHP\Foundation\Environment::singleton();
	}

	/**
	 * Pass the current Application object to the package container
	 *
	 * @return  Package
	 *
	 * @since  2.0.0
	 */
	public function setApp(Application $app)
	{
		$this->app = $app;

		return $this;
	}

	/**
	 * Set a package as required for this package
	 *
	 * @param   string|array  $package  name of the package (in the same path) or array(name, path)
	 *
	 * @return  Package
	 *
	 * @since  2.0.0
	 */
	public function requirePackage($package)
	{
		if ( ! is_array($package))
		{
			if (empty($this->path))
			{
				throw new \RuntimeException('The package path must be set before you can require additional packages');
			}

			$package = array($package, $this->path);
		}

		$this->packages[$package] = $package;

		return $this;
	}

	/**
	 * Attempts to find a controller, loads the class and returns the classname if found
	 *
	 * @param   string  $type  for example: controller or task
	 * @param   string  $path
	 *
	 * @return  bool|string
	 *
	 * @since  2.0.0
	 */
	public function findClass($type, $path)
	{
		// if the routable property is a string then this requires a trigger segment to be findable
		if (is_string($this->findTrigger))
		{
			// if string trigger isn't found at the beginning return false
			if (strpos(strtolower($path), strtolower($this->findTrigger).'/') !== 0)
			{
				return false;
			}
			// strip trigger from classname
			$path = substr($path, strlen($this->findTrigger) + 1);
		}

		// Build the namespace for the controller
		$namespace = $this->namespace;
		if ($pos = strpos($path, '/'))
		{
			$module = substr($path, 0, $pos).'/';
			if (isset($this->modules[$module]))
			{
				$namespace  .= $this->modules[$module];
				$path        = substr($path, $pos + 1);
			}
		}

		$path = $namespace.$this->classTypePrefix($type).str_replace('/', '_', $path);

		if ($this->loadClass($path))
		{
			return $path;
		}

		return false;
	}

	/**
	 * Change a special class type prefix
	 *
	 * @param   string  $type
	 * @param   string  $prefix
	 * @return  Package
	 *
	 * @since  2.0.0
	 */
	public function setClassTypePrefix($type, $prefix)
	{
		$this->classPrefixes[strtolower($type)] = $prefix;
		return $this;
	}

	/**
	 * Get the class prefix for a specific type
	 *
	 * @param   string  $type
	 *
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function classTypePrefix($type)
	{
		$type = strtolower($type);
		return isset($this->classPrefixes[$type]) ? $this->classPrefixes[$type] : '';
	}

	/**
	 * Attempt to load a class from the package
	 *
	 * @param   string  $class
	 * @return  bool
	 *
	 * @since  2.0.0
	 */
	public function loadClass($class)
	{
		// Save the original classname
		$original = $class;

		// Check if the class path was registered with the Package
		if (isset($this->classes[$class]))
		{
			require $this->classes[$class];
			return true;
		}
		// Check if the request class is an alias registered with the Package
		elseif (isset($this->classAliases[$class]))
		{
			class_alias($this->classAliases[$class], $class);
			return true;
		}

		// If a base namespace was set and doesn't match the class: fail
		if ($this->namespace === false
			or ($this->namespace and strpos($class, $this->namespace) !== 0))
		{
			return false;
		}

		// Anything further will be relative to the base namespace
		$class = substr($class, strlen($this->namespace));

		// Check if any of the modules' namespaces matches the class and make it relative on such a match
		$path = $this->path;
		foreach ($this->modules as $mPath => $mNamespace)
		{
			if (strpos($class, $mNamespace) === 0)
			{
				$class  = substr($class, strlen($mNamespace));
				$path  .= 'modules/'.$mPath.'/';
				break;
			}
		}
		$path = $this->classToPath($original, $class, $path.'classes/');

		// When found include the file and return success
		if (is_file($path))
		{
			require $path;
			return true;
		}

		// ... still here? Failure.
		return false;
	}

	/**
	 * Converts a classname to a path using PSR-0 conventions
	 *
	 * NOTE: using the base namespace setting and usage of modules break PSR-0 convention. The paths are expected
	 * relative to the base namespace when used and optionally relative to the module's (sub)namespace.
	 *
	 * @param   string  $fullName  full classname
	 * @param   string  $class     classname relative to base/module namespace
	 * @param   string  $basePath
	 *
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	protected function classToPath($fullName, $class, $basePath)
	{
		return $basePath.$this->env->psrClassToPath($this->relativeClassLoad ? $class : $fullName);
	}

	/**
	 * Set a base path for the package
	 *
	 * @param   string  $path
	 *
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
	 *
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
	 * Gets routability of this package
	 *
	 * @return  bool  whether or not this package is routeable
	 *
	 * @since  2.0.0
	 */
	public function getRoutable()
	{
		return $this->routable;
	}

	/**
	 * Assigns a name to this package
	 *
	 * @param   string  $name
	 *
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
	 *
	 * @return  Package
	 *
	 * @since  2.0.0
	 */
	public function setNamespace($namespace)
	{
		$this->namespace = $namespace ? trim($namespace, '\\').'\\' : $namespace;

		return $this;
	}

	/**
	 * Sets whether a class's path is relative to the main namespace of this
	 * package (true) or normal PSR-0 (false)
	 *
	 * @param   bool  $compliance
	 *
	 * @return  Package
	 *
	 * @since  2.0.0
	 */
	public function setRelativeClassLoad($compliance)
	{
		$this->relativeClassLoad = (bool) $compliance;
		return $this;
	}

}
