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

/**
 * Lang Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Lang extends Base
{
	/**
	 * Forge a new language object
	 *
	 * @param  $name      name of the instance
	 *
	 * @returns	Config
	 *
	 * @since  2.0.0
	 */
	public static function forge($name)
	{
		$instance = static::getDic()->multiton('config', 'lang-'.$name);
		return $instance;
	}

	/**
	 * Returns currently active language.
	 *
	 * @return   string    currently active language
	 */
	public static function getActive()
	{
		return \Config::get('lang.current', \Config::get('lang.fallback', 'en'));
	}

	/**
	 * Sets the  currently active language.
	 *
	 * @param   string    new active language
	 */
	public static function setActive($language)
	{
		\Config::set('lang.current', $language);
	}

	/**
	 * Returns a (dot notated) language string
	 *
	 * @param   string|array  $line     key or list of keys
	 * @param   array         $params   array of params to str_replace
	 * @param   mixed         $default  default value to return
	 *
	 * @return  mixed                    either the line or default when not found
	 *
	 * @since 1.0.0
	 */
	public static function get($line, array $params = array(), $default = null)
	{
		// create the list of keys to search
		if ( ! is_array($line))
		{
			$line = array($line);
		}

		// look for a match
		foreach($line as $key)
		{
			if (($found = static::getInstance()->get($key, '__FAIL__')) !== '__FAIL__')
			{
				break;
			}

		}

		// no match
		if ($found === '__FAIL__')
		{
			// do we have a default?
			if (func_num_args() == 4)
			{
				$found = $default;
			}
			else
			{
				// get the globally configured default
				$found = \Config::get('lang.default', '');
				$found = str_replace('{key}', array_shift($line), $found);
			}
		}

		// stuff in any parameters passed, and return the result
		return \Str::tr($found, $params);
	}

	/**
	 * Sets a (dot notated) language string
	 *
	 * @param    string       $line      a (dot notated) language key
	 * @param    mixed        $value     the language string
	 * @param    string       $group     group
	 * @return   void                    the set() result
	 */
	public static function set($line, $value, $group = null)
	{
		$group === null or $line = $group.'.'.$line;

		return static::getInstance()->set($line, $value);
	}

	/**
	 * Deletes a (dot notated) language string
	 *
	 * @param    string       $item      a (dot notated) language key
	 * @param    string       $group     group
	 * @return   array|bool              the delete() result, success boolean
	 */
	public static function delete($item, $group = null)
	{
		$group === null or $line = $group.'.'.$line;

		return static::getInstance()->delete($line, $value);
	}

	/**
	 * Get the current object instance for this Facade, optionally get a specific
	 * language instance
	 *
	 * @since  2.0.0
	 */
	public static function getInstance($lang = null)
	{
		return \Application::getInstance()->getComponent()->getLanguage($lang);
	}
}
