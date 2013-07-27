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
 * Environment
 *
 * Sets up the environment for PHP and the FuelPHP framework.
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Environment
{
	/**
	 * @var  string  application
	 *
	 * @since  2.0.0
	 */
	protected $app;

	/**
	 * @var  string  name of the current environment
	 *
	 * @since  2.0.0
	 */
	protected $name = 'development';

	/**
	 * @var  string  base url
	 *
	 * @since  2.0.0
	 */
	protected $baseUrl = null;

	/**
	 * @var  string
	 *
	 * @since  2.0.0
	 */
	protected $indexFile = null;

	/**
	 * @var  array  container for environment variables
	 *
	 * @since  2.0.0
	 */
	protected $vars = array();

	/**
	 * @var  array  paths registered in the global environment
	 *
	 * @since  2.0.0
	 */
	protected $paths = array();

	/**
	 * Setup the framework environment. This will include all required global
	 * classes, paths, and other configuration required to start the app.
	 *
	 * @throws  none
	 * @return  void
	 *
	 * @since  2.0.0
	 */
	public function __construct($app, $environment, $config)
	{
		$this->app = $app;

		// store some initial environment values
		$this->vars['initTime'] = defined('FUEL_INIT_TIME') ? FUEL_INIT_TIME : microtime(true);
		$this->vars['initMem']  = defined('FUEL_INIT_MEM') ? FUEL_INIT_MEM : memory_get_usage();


		// fetch URL data from the config, construct it if not set
		if ($this->baseUrl = $config->baseUrl === null)
		{
			$this->baseUrl = \Input::getInstance()->getBaseUrl();
		}
		$this->indexFile = $config->indexFile;

		// store the application path
		$this->addPath($this->app->getName(), $this->app->getPath());

		// load the defined environments
		$environments = $this->app->getPath().DS.'environments.php';
		if (file_exists($environments))
		{
			$environments = require $environments;
		}
		else
		{
			$environments = array();
		}

		// run default environment
		$finishCallbacks = array();
		if (isset($environments['default']))
		{
			$finishCallbacks[] = call_user_func($environments['default'], $this);
		}

		// run specific environment config when given
		if (isset($environments[$environment]))
		{
			$finishCallbacks[] = call_user_func($environments[$environment], $this);
		}

		// detect the base URL from global when not given
		if (is_null($this->baseUrl))
		{
			if (\Fuel::isCli())
			{
				throw new \Exception('TODO');
			}
			else
			{
				$this->baseUrl = \Input::getInstance()->getBaseUrl();
			}
		}

		// run environment callbacks to finish up
		foreach ($finishCallbacks as $cb)
		{
			is_callable($cb) and call_user_func($cb, $this);
		}
	}

	/**
	 * Get a property that is available through a getter
	 *
	 * @param   string  $property
	 * @return  mixed
	 * @throws  \OutOfBoundsException
	 *
	 * @since  2.0.0
	 */
	public function __get($property)
	{
		if (method_exists($this, $method = 'get'.ucfirst($property)))
		{
			return $this->{$method}();
		}

		throw new \OutOfBoundsException('Property "'.$property.'" not available on the environment.');
	}

	/**
	 * Set a global variable
	 *
	 * @param   string  $name
	 * @param   mixed   $value
	 * @return  Environment  to allow method chaining
	 *
	 * @since  2.0.0
	 */
	public function setVar($name, $value)
	{
		// store the variable passed
		$this->vars[$name] = $value;

		return $this;
	}

	/**
	 * Get a global variable
	 *
	 * @param   string  $name
	 * @param   mixed   $default  value to return when name is unknown
	 * @return  mixed
	 *
	 * @since  2.0.0
	 */
	public function getVar($name = null, $default = null)
	{
		// return all when no arguments were given
		if (func_num_args() == 0)
		{
			return $this->vars;
		}

		// check if value exists, return default when it doesn't
		if ( ! isset($this->vars[$name]))
		{
			return $default;
		}

		return $this->vars[$name];
	}

	/**
	 * Fetch the full path for a given pathname
	 *
	 * @param   string  $name
	 * @return  string
	 * @throws  \OutOfBoundsException
	 *
	 * @since  2.0.0
	 */
	public function getPath($name)
	{
		if ( ! isset($this->paths[$name]))
		{
			throw new \OutOfBoundsException('Unknown path requested: '.$name);
		}

		return $this->paths[$name];
	}

	/**
	 * Attempt make the path relative to a registered path
	 *
	 * @param   string  $path
	 * @return  string
	 *
	 * @since  1.0.0
	 */
	public function cleanPath($path)
	{
		$path = str_replace('\\/', '/', $path);
		foreach ($this->paths as $name => $p)
		{
			if (strpos($path, $p) === 0)
			{
				return $name.'::'.substr(str_replace('\\', '/', $path), strlen($p));
			}
		}
		return $path;
	}

	/**
	 * Register a new named path
	 *
	 * @param   string       $name       name for the path
	 * @param   string       $path       the full path
	 * @param   bool         $overwrite  whether or not overwriting existing name is allowed
	 * @return  Environment  to allow method chaining
	 * @throws  \OutOfBoundsException
	 *
	 * @since  2.0.0
	 */
	public function addPath($name, $path, $overwrite = false)
	{
		if ( ! $overwrite and isset($this->paths[$name]))
		{
			throw new \OutOfBoundsException('Already a path registered for name: '.$name);
		}

		$this->paths[$name] = rtrim(str_replace('\\', '/', $path), '/\\').'/';

		return $this;
	}

	/**
	 * Get the baseUrl
	 *
	 * @return  string|null  determined base url
	 *
	 * @since  2.0.0
	 */
	public function getBaseUrl()
	{
		return $this->baseUrl;
	}
}
