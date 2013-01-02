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
	 * @var  array  container for environment variables
	 *
	 * @since  2.0.0
	 */
	protected $vars = array();

	/**
	 * @var  string  $appPath  path the the application folder
	 *
	 * @since  2.0.0
	 */
	protected $appPath = null;

	/**
	 * @var  FuelPHP\DependencyInjection\Container  $dic  global Dependency Injection container
	 *
	 * @since  2.0.0
	 */
	protected $dic = null;

	/**
	 * @var  \FuelPHP\Foundation\Input  the input container
	 *
	 * @since  2.0.0
	 */
	protected $input = null;

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
	 * Return the environmental Input object
	 *
	 * @return  FuelPHP\Foundation\Input
	 *
	 * @since  2.0.0
	 */
	public function getInput()
	{
		return $this->input;
	}

	/**
	 * Return the environmental DiC
	 *
	 * @return  \Fuel\DiC\Base
	 *
	 * @since  2.0.0
	 */
	public function getDiC()
	{
		return $this->dic;
	}

	/**
	 * Set the environmental DiC
	 *
	 * @param   Container  $dic
	 * @return  Environment  to allow method chaining
	 *
	 * @since  2.0.0
	 */
	public function setDiC(Container $dic)
	{
		$this->dic = $dic;

		return $this;
	}

	/**
	 * Set the path to the application folder, and verify it
	 *
	 * @param  string  $appPath  path where the application to be launched can be found
	 *
	 * @throws  InvalidArgumentException  if the path passed does not exist
	 * @return  $this
	 */
	public function setApp($appPath)
	{
		// store the application path
		$this->appPath = realpath($appPath);

		// make sure it exists
		if (empty($this->appPath) or ! is_dir($this->appPath))
		{
			throw new \InvalidArgumentException('Application path given does not exist');
		}

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
	 * Allows the overwriting of the default environment settings
	 *
	 * @param   array  $config
	 * @return  Environment  to allow method chaining
	 * @throws  \RuntimeException
	 *
	 * @since  2.0.0
	 */
	public function setConfig(array $config)
	{
		// configure the localization options for PHP
		$this->setLocale($this->locale);
		$this->setTimezone($this->timezone);

		// detects and configures the PHP Environment
		$this->setPhpEnv();

		// load the input container if not yet set
// CHECKME
		is_null($this->input) and $this->input = $this->dic->resolve('FuelPHP\\Foundation\\Input', false);

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
