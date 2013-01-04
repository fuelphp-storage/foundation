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

use FuelPHP\DependencyInjection\Container;

/**
 * Environment
 *
 * Sets up the environment for PHP and the FuelPHP framework.
 *
 * @package  FuelPHP\Foundation
 *
 * @since  2.0.0
 */
class Environment
{
	/**
	 * @constant  string  version identifier
	 *
	 * @since  2.0.0
	 */
	const VERSION = '2.0-dev';

	/**
	 * @var  string  name of the current environment
	 *
	 * @since  2.0.0
	 */
	public $name = 'development';

	/**
	 * @var  string|null  optional overwrite for system environment setting
	 *
	 * @since  2.0.0
	 */
	public $locale = null;

	/**
	 * @var  string|null  timezone name for php.net/timezones
	 *
	 * @since  2.0.0
	 */
	public $timezone = 'UTC';

	/**
	 * @var  bool  whether or not usage of MBSTRING extension is enabled
	 *
	 * @since  2.0.0
	 */
	public $mbstring = null;

	/**
	 * @var  string|null  character encoding
	 *
	 * @since  2.0.0
	 */
	public $encoding = 'UTF-8';

	/**
	 * @var  bool  whether this is run through the command line
	 *
	 * @since  2.0.0
	 */
	public $isCli = false;

	/**
	 * @var  bool  Readline is an extension for PHP that makes interactive with PHP much more bash-like
	 *
	 * @since  2.0.0
	 */
	public $readlineSupport = false;

	/**
	 * @var  string  base url
	 *
	 * @since  2.0.0
	 */
	public $baseUrl = null;

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
	 * @var  array  appnames and their classnames
	 *
	 * @since  2.0.0
	 */
	protected $apps = array();

	/**
	 * @var  Application  $application  The main application container
	 *
	 * @since  2.0.0
	 */
	protected $application = null;

	/**
	 * @var  FuelPHP\DependencyInjection\Container  $dic  global Dependency Injection container
	 *
	 * @since  2.0.0
	 */
	protected $dic = null;

	/**
	 * @var  FuelPHP\Alias\Manager  $alias  global class alias manager
	 *
	 * @since  2.0.0
	 */
	protected $alias = null;

	/**
	 * @var  Input  the input container
	 *
	 * @since  2.0.0
	 */
	protected $input = null;

	/**
	 * @var  Application
	 *
	 * @since  2.0.0
	 */
	protected $activeApp;

	/**
	 * Setup the framework environment. This will include all required global
	 * classes, paths, and other configuration required to start the app.
	 *
	 * @throws  none
	 * @return  void
	 *
	 * @since  2.0.0
	 */
	public function __construct()
	{
		// store some initial environment values
		$this->vars['initTime'] = defined('FUEL_INIT_TIME') ? FUEL_INIT_TIME : microtime(true);
		$this->vars['initMem']  = defined('FUEL_INIT_MEM') ? FUEL_INIT_MEM : memory_get_usage();

		// create our instance of the alias manager
		$this->alias = new \FuelPHP\Alias\Manager;

		// and our instance of the DiC
		$this->dic = new \FuelPHP\DependencyInjection\Container;
	}

	/**
	 * Allows the overwriting of the environment settings, should only be run once
	 *
	 * @param   array  $config
	 * @return  Environment  to allow method chaining
	 * @throws  \RuntimeException
	 *
	 * @since  2.0.0
	 */
	public function init(array $config = array())
	{
		if ($this->initialized)
		{
			throw new \RuntimeException('Environment config shouldn\'t be initiated more than once.', E_USER_ERROR);
		}

		// application path must be given
		if ( ! isset($config['path']))
		{
			throw new \RuntimeException('The application path must be provided to Environment.', E_USER_ERROR);
		}

		$config['path'] = realpath($config['path']);
		// make sure it exists
		if (empty($config['path']) or ! is_dir($config['path']))
		{
			throw new \InvalidArgumentException('The application path does not exist. Can not initialize the application.');
		}

		// application path must be given
		if ( ! isset($config['application']))
		{
			throw new \RuntimeException('The name of the application package must be provided to the Environment.', E_USER_ERROR);
		}

		// store the application path
		$this->addPath($config['application'], $config['path'].'/');

		// store the application path
		$this->application = $config['application'];

		// make sure it exists
		if (empty($this->application) or ! is_dir($config['path'].'/'.$this->application))
		{
			throw new \InvalidArgumentException('The application can not be found in the path given. Can not initialize the application.');
		}

		// set (if array) or load (when empty/string) environments
		$environments = isset($config['environments'])
			? $config['environments']
			: $config['path'].'/'.$this->application.'/environments.php';

		is_string($environments) and $environments = require $environments;

		unset($config['environments']);

		// run default environment
		$finishCallbacks = array();
		if (isset($environments['__default']))
		{
			$finishCallbacks[] = call_user_func($environments['__default'], $this);
		}

		// run specific environment config when given
		$config['name'] = isset($config['name']) ? $config['name'] : 'development';
		if (isset($environments[$config['name']]))
		{
			$finishCallbacks[] = call_user_func($environments[$config['name']], $this);
		}

		// set any other configuration values passed, may overwrite environment settings!
		foreach ($config as $key => $val)
		{
			property_exists($this, $key) and $this->{$key} = $val;
		}

		// load the input container if not yet set
		is_null($this->input) and $this->input = \FuelPHP::resolve('Input');

		// and import the globals
		$this->input->fromGlobals();

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

		// we're done
		$this->initialized = true;

		return $this;
	}

	/**
	 * Load application and return instantiated
	 *
	 * @param   string    $appName
	 * @param   \Closure  $config
	 * @return  Application\Base
	 * @throws  \OutOfBoundsException
	 *
	 * @since  2.0.0
	 */
	public function loadApplication($appName = null, $appPath = null, Closure $config = null)
	{
		is_null($appName) and $appName = $this->application;
		is_null($appPath) and $appPath = $this->getPath($appName);

		$application = \FuelPHP::resolve('Application', null, $appName, $appPath);

		$this->apps[$appName] = $application;

		is_null($this->activeApp) and $this->activeApp = $application;

		return $application;
	}

	/**
	 * Sets the current active Application
	 *
	 * @param   Application  $app
	 *
	 * @return  Environment
	 *
	 * @since  2.0.0
	 */
	public function setActiveApplication(Application $app = null)
	{
		$this->activeApp = $app;

		return $this;
	}

	/**
	 * Fetches the current active Application
	 *
	 * @return  Application
	 *
	 * @since  2.0.0
	 */
	public function getActiveApplication()
	{
		return $this->activeApp;
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
		$path = str_replace('\\', '/', $path);
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
	 * Return the Alias manager
	 *
	 * @return  FuelPHP\Alias\Manager
	 *
	 * @since  2.0.0
	 */
	public function getAlias()
	{
		return $this->alias;
	}

	/**
	 * sets the Alias manager
	 *
	 * @return  Environment
	 *
	 * @since  2.0.0
	 */
	public function setAlias($alias)
	{
		$this->alias = $alias;

		return $this;
	}

	/**
	 * Return the DiC
	 *
	 * @return  FuelPHP\DependencyInjection\Container
	 *
	 * @since  2.0.0
	 */
	public function getDiC()
	{
		return $this->dic;
	}

	/**
	 * sets the DiC
	 *
	 * @return  Environment
	 *
	 * @since  2.0.0
	 */
	public function setDic($dic)
	{
		$this->dic = $dic;

		return $this;
	}

	/**
	 * Return the Profiler
	 *
	 * @return  Profiler
	 *
	 * @since  2.0.0
	 */
	public function getProfiler()
	{
		return \FuelPHP::resolve('Profiler');
	}

	/**
	 * Return the main application object
	 *
	 * @return  Application
	 *
	 * @since  2.0.0
	 */
	public function getApplication()
	{
		return $this->application;
	}

	/**
	 * Return the global Input container object
	 *
	 * @return  Input
	 *
	 * @since  2.0.0
	 */
	public function getInput()
	{
		return $this->input;
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

		// detect the base URL when not given
		if (is_null($this->baseUrl) and ! $this->isCli)
		{
			$this->baseUrl = $this->detectBaseUrl();
		}

		// when mbstring setting was not given default to availability
		! is_bool($this->mbstring) and $this->mbstring = function_exists('mb_get_info');
		$this->setEncoding($this->encoding);

		// setup the shutdown, error & exception handlers
		$env = $this;
		register_shutdown_function(function () use ($env)
		{
			$error = error_get_last();

			// No error? Nothing to shutdown
			if ( ! $error)
			{
				return true;
			}

			// Do nothing when the error isn't part of the error_reporting level
			if ((error_reporting() & $error['type']) !== $error['type'])
			{
				return true;
			}

			$error = new \ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']);
// CHECKME
//			if (($app = $env->getActiveApplication()) and $handler = $app->getErrorHandler())
//			{
//				return $handler->handle($error);
//			}
			exit($env->isCli ? $error : nl2br($error));
		});
		set_error_handler(function ($severity, $message, $filepath, $line) use ($env)
		{
			// Do nothing when the error isn't part of the error_reporting level
			if ((error_reporting() & $severity) !== $severity)
			{
				return true;
			}

			$error = new \ErrorException($message, $severity, 0, $filepath, $line);
// CHECKME
//			if (($app = $env->getActiveApplication()) and $handler = $app->getErrorHandler())
//			{
//				return $handler->handle($error);
//			}
			exit($env->isCli ? $error : nl2br($error));
		});
		set_exception_handler(function (\Exception $e) use ($env)
		{
			if (method_exists($e, 'handle'))
			{
				return $e->handle();
			}
// CHECKME
//			if (($app = $env->getActiveApplication()) and $handler = $app->getErrorHandler())
//			{
//				return $handler->handle($error);
//			}

			! $env->isCli and print('<pre>');
			echo $e;
			! $env->isCli and print('</pre>');
			exit($e->getCode() ?: 1);
		});
	}

	/**
	 * Convert a classname to a path as per PSR-0 rules
	 *
	 * @param   $class
	 *
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function psrClassToPath($class)
	{
		$file  = '';
		if ($last_ns_pos = strripos($class, '\\'))
		{
			$namespace = substr($class, 0, $last_ns_pos);
			$class = substr($class, $last_ns_pos + 1);
			$file = str_replace('\\', '/', $namespace).'/';
		}
		$file .= str_replace('_', '/', $class).'.php';

		return $file;
	}

	/**
	 * Generates a base url.
	 *
	 * @return  string  the base url
	 *
	 * @since  2.0.0
	 */
	protected function detectBaseUrl()
	{
		$baseUrl = '';
		if (isset($this->input->server['HTTP_HOST']))
		{
			$baseUrl .= $this->input->getScheme().'://'.$this->input->server['HTTP_HOST'];
		}
		if (isset($this->input->server['SCRIPT_NAME']))
		{
			$baseUrl .= str_replace('\\', '/', dirname($this->input->server['SCRIPT_NAME']));

			// Add a slash if it is missing
			$baseUrl = rtrim($baseUrl, '/').'/';
		}

		return $baseUrl;
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
}
