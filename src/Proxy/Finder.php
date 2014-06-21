<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation\Proxy;

/**
 * Finder Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  1.0.0
 */
class Finder extends Base
{
	/**
	 * Forges new Finders.
	 *
	 * @param  array   $path  paths
	 * @param  string  $defaultExtension  default file extension
	 * @param  string  $root              root restriction

	 * @return  Fuel\Filesystem\Finder
	 */
	public static function forge()
	{
		return static::getDic()->resolve('finder', func_get_args());
	}

	/**
	 * An alias for Finder::instance()->locate();
	 *
	 * @param   string  $dir       Directory to look in
	 * @param   string  $file      File to find
	 * @param   string  $ext       File extension
	 * @param   bool    $multiple  Whether to find multiple files
	 * @param   bool    $cache     Whether to cache this path or not
	 * @return  mixed  Path, or paths, or false
	 */
	public static function search($dir, $file, $ext = 'php', $multiple = false, $cache = true)
	{
		$finder = static::forge(array($dir), trim($ext, '.'));

		if (($result = $finder->findCached($multiple?'all':'one', $file)) === null)
		{
			$result = $multiple ? $finder->findAll($file, false, false, 'file') : $finder->find($file, false, false, 'file');
		}

		if ($cache and $result)
		{
			$finder->cache($multiple?'all':'one', $file, false, $result, array($dir));
		}

		return $result;
	}

	/**
	 * Get the object instance for this Facade
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		// this facade provides no instances
		return null;
	}
}
