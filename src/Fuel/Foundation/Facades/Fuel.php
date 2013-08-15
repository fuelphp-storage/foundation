<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation\Facades;

use Fuel\Config\Container;

/**
 * Fuel Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  1.0.0
 */
class Fuel extends Base
{
	/**
	 * @var  string  The global version of framework
	 *
	 * @since  1.0.0
	 */
	const VERSION = '2.0-dev';

	/**
	 * Locale to be used for this framework instance
	 *
	 * @since  1.0.0
	 */
	protected static $locale = 'en_US';

	/**
	 * Timezone to be used for this framework instance
	 *
	 * @since  1.0.0
	 */
	protected static $timezone = 'UTC';

	/**
	 * Character encoding to be used for this framework instance
	 *
	 * @since  1.0.0
	 */
	protected static $encoding = 'UTF-8';

	/**
	 * Whether we're running in CLI mode
	 *
	 * @since  1.0.0
	 */
	protected static $isCli = false;

	/**
	 * Whether we have readline support in CLI mode
	 *
	 * @since  1.0.0
	 */
	protected static $readlineSupport = false;

	/**
	 * Initialization, lets get this framework going!
	 *
	 * @since  2.0.0
	 */
	public static function initialize(Container $config)
	{
		// Start up output buffering
		ob_start($config->get('ob_callback', null));

		// configure the localization options for PHP
		static::$encoding = $config->get('encoding', static::$encoding);
		static::setEncoding(static::$encoding);

		static::$locale = $config->get('locale', static::$locale);
		static::setLocale(static::$locale);

		static::$timezone = $config->get('default_timezone') ?: date_default_timezone_get();
		static::setTimezone(static::$timezone);

		// determine the Cli state
		static::$isCli = (bool) defined('STDIN');
		static::$isCli and static::$readlineSupport = extension_loaded('readline');
	}

	/**
	 * Set the character encoding (only when mbstring is enabled)
	 *
	 * @param   string|null  $encoding  encoding name
	 * @return  Environment  to allow method chaining
	 *
	 * @since  2.0.0
	 */
	public static function setEncoding($encoding)
	{
		static::$encoding = $encoding;
		if (MBSTRING and static::$encoding)
		{
			mb_internal_encoding(static::$encoding);
		}
	}

	/**
	 * Get the character encoding
	 *
	 * @return  string|null  $encoding  encoding name
	 *
	 * @since  2.0.0
	 */
	public static function getEncoding()
	{
		return static::$encoding;
	}

	/**
	 * Set the locale
	 *
	 * @param   string|null  $locale  locale name (OS dependent)
	 * @return  Environment  to allow method chaining
	 *
	 * @since  2.0.0
	 */
	public static function setLocale($locale)
	{
		static::$locale = $locale;
		if (static::$locale !== null)
		{
			if ( ! setlocale(LC_ALL, static::$locale))
			{
				throw new \Exception('The locale "'.$locale.'" is not installed on this server');
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
	public static function getLocale()
	{
		return static::$locale;
	}

	/**
	 * Set the timezone
	 *
	 * @param   string|null  $timezone  timezone name (http://php.net/timezones)
	 * @return  Environment  to allow method chaining
	 *
	 * @since  2.0.0
	 */
	public static function setTimezone($timezone)
	{
		static::$timezone = $timezone;

		// set a default timezone if one is defined
		try
		{
			date_default_timezone_set(static::$timezone);
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
	public static function getTimezone()
	{
		return static::$timezone;
	}

	/**
	 * Are we in CLI mode?
	 *
	 * @return  bool
	 *
	 * @since  2.0.0
	 */
	public static function isCli()
	{
		return static::$isCli;
	}

	/**
	 * Do we have readline support in CLI mode?
	 *
	 * @return  bool
	 *
	 * @since  2.0.0
	 */
	public static function hasReadlineSupport()
	{
		return static::$readlineSupport;
	}

	/**
	 * Get the object instance for this Facade
	 *
	 * @since  2.0.0
	 */
	protected static function getInstance()
	{
		return null;
	}
}

