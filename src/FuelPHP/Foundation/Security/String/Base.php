<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    FuelPHP\Foundation
 * @version    2.0
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 */

namespace FuelPHP\Foundation\Security\String;

/**
 * Base String Security class
 *
 * Basis for classes dealing with securing strings.
 *
 * @package  Fuel\Kernel
 *
 * @since  2.0.0
 */
abstract class Base
{
	/**
	 * @var  \FuelPHP\Foundation\Environment
	 *
	 * @since  2.0.0
	 */
	public $env;

	/**
	 * @var  \FuelPHP\Foundation\Application
	 *
	 * @since  2.0.0
	 */
	protected $app;

	/**
	 * Constructor
	 *
	 * @since  2.0.0
	 */
	public function __construct()
	{
		// set the environment variable necessary for the package loader object
		$this->env = \FuelPHP\Foundation\Environment::singleton();

		$this->app = $this->env->getActiveApplication();
	}

	/**
	 * Clean string, object or array
	 *
	 * @param   mixed  $input
	 * @return  mixed
	 * @throws  \RuntimeException
	 *
	 * @since  2.0.0
	 */
	public function clean($input)
	{
		static $alreadyCleaned = array();

		// Nothing to escape for non-string scalars, or for already processed values
		if (is_bool($input) or is_int($input) or is_float($input) or in_array($input, $alreadyCleaned, true))
		{
			return $input;
		}

		if (is_string($input))
		{
			$input = $this->secure($input);
		}
		elseif (is_array($input) or ($input instanceof \Iterator and $input instanceof \ArrayAccess))
		{
			// Add to $already_cleaned variable when object
			is_object($input) and $alreadyCleaned[] = $input;

			foreach ($input as $k => $v)
			{
				$input[$k] = $this->clean($v);
			}
		}
		elseif ($input instanceof \Iterator or get_class($input) == 'stdClass')
		{
			// Add to $already_cleaned variable
			$alreadyCleaned[] = $input;

			foreach ($input as $k => $v)
			{
				$input->{$k} = $this->secure($v);
			}
		}
		elseif (is_object($input))
		{
			// Check if the object is whitelisted and return when that's the case
			foreach ($this->app->config->get('security.whitelistedClasses', array()) as $class)
			{
				if (is_a($input, $class))
				{
					// Add to $already_cleaned variable
					$alreadyCleaned[] = $input;

					return $input;
				}
			}

			// Throw exception when it wasn't whitelisted and can't be converted to String
			if ( ! method_exists($input, '__toString'))
			{
				throw new \RuntimeException('Object class "'.get_class($input).'" could not be converted to string or '.
					'sanitized as ArrayAccess. Whitelist it in security.whitelisted_classes in app/config/config.php '.
					'to allow it to be passed unchecked.');
			}

			$input = $this->clean(strval($input));
		}

		return $input;
	}

	/**
	 * Secure string
	 *
	 * @param   string
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	abstract protected function secure($input);
}
