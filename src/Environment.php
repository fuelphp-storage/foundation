<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
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
	 * Locale to be used for this environment instance
	 *
	 * @since  1.0.0
	 */
	protected $locale = null;

	/**
	 * Timezone to be used for this environment instance
	 *
	 * @since  1.0.0
	 */
	protected $timezone = 'UTC';

	/**
	 * Character encoding to be used for this environment instance
	 *
	 * @since  1.0.0
	 */
	protected $encoding = 'UTF-8';

	/**
	 * Whether we're running in CLI mode
	 *
	 * @since  1.0.0
	 */
	protected $isCli = false;

	/**
	 * Whether we have readline support in CLI mode
	 *
	 * @since  1.0.0
	 */
	protected $readlineSupport = false;

	/**
	 * @var  array  container for environment variables
	 *
	 * @since  2.0.0
	 */
	protected $vars = array();

	/**
	 * Setup the framework environment. This will include all required global
	 * classes, paths, and other configuration required to start the app.
	 *
	 * @return  void
	 *
	 * @since  2.0.0
	 */
	public function __construct($environment, $app)
	{
		// store some initial environment values
		$this->vars['initTime'] = defined('FUEL_INIT_TIME') ? FUEL_INIT_TIME : microtime(true);
		$this->vars['initMem']  = defined('FUEL_INIT_MEM') ? FUEL_INIT_MEM : memory_get_usage();

		// store the objects passed
		$this->app = $app;

		// fetch URL data from the config, construct it if not set
		if ($this->app->getConfig()->baseUrl === null)
		{
			$this->app->getConfig()->baseUrl = $this->app->getInput()->getBaseUrl();
		}

		// set the environment
		$this->setName($environment);

		// configure the localization options for PHP
		$this->encoding = $this->app->getConfig()->get('encoding', $this->encoding);
		$this->setEncoding($this->encoding);

		$this->locale = $this->app->getConfig()->get('locale', $this->locale);
		$this->setLocale($this->locale);

		$this->timezone = $this->app->getConfig()->get('default_timezone') ?: date_default_timezone_get();
		$this->setTimezone($this->timezone);

		// determine the Cli state
		if ($this->isCli = (bool) defined('STDIN'))
		{
			$this->readlineSupport = extension_loaded('readline');
		}
		else
		{
			// start up output buffering if needed
			ob_start($this->app->getConfig()->get('ob_callback', null));
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

		throw new \OutOfBoundsException('FOU-005: Property ['.$property.'] not available on the environment.');
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
	 * Sets and initializes the environment
	 *
	 * @since  2.0.0
	 */
	public function setName($name)
	{
		// local storage, to prevent loading multiple times
		static $environments = array();

		// store the environment name
		$this->name = $name;

		// load the defined environments
		if (empty($environments))
		{
			// get the apps main components paths
			$paths = $this->app->getRootComponent()->getPaths();

			foreach($paths as $path)
			{
				if (file_exists($path .= DS.'config'.DS.'environments.php'))
				{
					$environments = array_merge($environments, include $path);
				}
			}
		}

		// run default environment
		$finishCallbacks = array();
		if (isset($environments['default']))
		{
			$finishCallbacks[] = call_user_func($environments['default'], $this);
		}

		// run specific environment config when given
		if (isset($environments[$name]))
		{
			$finishCallbacks[] = call_user_func($environments[$name], $this);
		}

		// run environment callbacks to finish up
		foreach ($finishCallbacks as $cb)
		{
			if (is_callable($cb))
			{
				call_user_func($cb, $this);
			}
		}
	}

	/**
	 * Returns the environment name
	 *
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Set the character encoding (only when mbstring is enabled)
	 *
	 * @param   string|null  $encoding  encoding name
	 *
	 * @since  2.0.0
	 */
	public function setEncoding($encoding)
	{
		$this->encoding = $encoding;
		if (MBSTRING and $this->encoding)
		{
			mb_internal_encoding($this->encoding);
		}
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
		if ($this->locale !== null)
		{
			if ( ! setlocale(LC_ALL, $this->locale))
			{
				throw new \Exception('FOU-018: The locale ['.$locale.'] is not installed on this server');
			}
		}
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
	 *
	 * @since  2.0.0
	 */
	public function setTimezone($timezone)
	{
		$this->timezone = $timezone;

		// set a default timezone if one is defined
		try
		{
			date_default_timezone_set($this->timezone);
		}
		catch (\Exception $e)
		{
			date_default_timezone_set('UTC');
			throw new \Exception($e->getMessage());
		}
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
	 * Are we in CLI mode?
	 *
	 * @return  bool
	 *
	 * @since  2.0.0
	 */
	public function isCli()
	{
		return $this->isCli;
	}

	/**
	 * Do we have readline support in CLI mode?
	 *
	 * @return  bool
	 *
	 * @since  2.0.0
	 */
	public function hasReadlineSupport()
	{
		return $this->readlineSupport;
	}
}
