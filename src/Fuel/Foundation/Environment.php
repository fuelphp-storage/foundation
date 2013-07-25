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
	 * @var  string|null  optional overwrite for system environment setting
	 *
	 * @since  2.0.0
	 */
	protected $locale = null;

	/**
	 * @var  string|null  timezone name for php.net/timezones
	 *
	 * @since  2.0.0
	 */
	protected $timezone = 'UTC';

	/**
	 * @var  bool  whether or not usage of MBSTRING extension is enabled
	 *
	 * @since  2.0.0
	 */
	protected $mbstring = null;

	/**
	 * @var  string|null  character encoding
	 *
	 * @since  2.0.0
	 */
	protected $encoding = 'UTF-8';

	/**
	 * @var  bool  whether this is run through the command line
	 *
	 * @since  2.0.0
	 */
	protected $isCli = false;

	/**
	 * @var  bool  Readline is an extension for PHP that makes interactive with PHP much more bash-like
	 *
	 * @since  2.0.0
	 */
	protected $readlineSupport = false;

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
	public $indexFile = null;

	/**
	 * @var  bool  flag to indicate we're initialized
	 *
	 * @since  2.0.0
	 */
	protected $initialized = false;

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


		// fetch URL data from the config
		$this->baseUrl = $config->baseUrl;
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

		// configure the localization options for PHP
		$this->setLocale($this->locale);
		$this->setTimezone($this->timezone);

		// detects and configures the PHP Environment
		$this->setPhpEnv();

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
	 * Set the character encoding (only when mbstring is enabled)
	 *
	 * @param   string|null  $encoding  encoding name
	 * @return  Environment  to allow method chaining
	 *
	 * @since  2.0.0
	 */
	public function setEncoding($encoding)
	{
		$this->encoding = $encoding;
		if ($this->mbstring and $this->encoding)
		{
			mb_internal_encoding($this->encoding);
		}

		return $this;
	}

	/**
	 * Get the character encoding
	 *
	 * @return  string|null  $encoding  encoding name
	 *
	 * @since  2.0.0
	 */
	public function getEncoding()
	{
		return $this->encoding;
	}

	/**
	 * Set the locale
	 *
	 * @param   string|null  $locale  locale name (OS dependent)
	 * @return  Environment  to allow method chaining
	 *
	 * @since  2.0.0
	 */
	public function setLocale($locale)
	{
		$this->locale = $locale;
		is_null($this->locale) or setlocale(LC_ALL, $this->locale);

		return $this;
	}

	/**
	 * Get the locale
	 *
	 * @return  string|null  locale name (OS dependent)
	 *
	 * @since  2.0.0
	 */
	public function getLocale()
	{
		return $this->locale;
	}

	/**
	 * Set the timezone
	 *
	 * @param   string|null  $timezone  timezone name (http://php.net/timezones)
	 * @return  Environment  to allow method chaining
	 *
	 * @since  2.0.0
	 */
	public function setTimezone($timezone)
	{
		$this->timezone = $timezone;
		$this->timezone and date_default_timezone_set($this->timezone);

		return $this;
	}

	/**
	 * Get the timezone
	 *
	 * @return  string|null  timezone name (http://php.net/timezones)
	 *
	 * @since  2.0.0
	 */
	public function getTimezone()
	{
		return $this->timezone;
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


	/**
	 * Detects and configures the PHP Environment
	 *
	 * @return  void
	 *
	 * @since  2.0.0
	 */
	protected function setPhpEnv()
	{
		// determine the Cli state
		$this->isCli = (bool) defined('STDIN');
		$this->isCli and $this->readlineSupport = extension_loaded('readline');

		// detect the base URL from global when not given
		if (is_null($this->baseUrl) and ! $this->isCli)
		{
			$this->baseUrl = \Input::getInstance()->getBaseUrl();
		}

		// when mbstring setting was not given default to availability
		! is_bool($this->mbstring) and $this->mbstring = function_exists('mb_get_info');
		$this->setEncoding($this->encoding);
	}
}
