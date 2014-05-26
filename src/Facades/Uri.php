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
 * Uri Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Uri extends Base
{
	/**
	 * Creates a url with the given uri, including the base url
	 *
	 * @param   string  $uri            The uri to create the URL for
	 * @param   array   $variables      Some variables for the URL
	 * @param   array   $get_variables  Any GET urls to append via a query string
	 * @param   bool    $secure         If false, force http. If true, force https
	 *
	 * @return  string
	 *
	 * @since  1.0.0
	 */
	public static function create($uri = null, $variables = array(), $get_variables = array(), $secure = null)
	{
		$url = '';
		$uri = $uri ?: static::string();

		// If the given uri is not a full URL
		if( ! preg_match("#^(http|https|ftp)://#i", $uri))
		{
			$url .= \Config::get('baseUrl');

			if ($index_file = \Config::get('indexFile'))
			{
				$url .= $index_file.'/';
			}
		}
		$url .= ltrim($uri, '/');

		// Add a url_suffix if defined and the url doesn't already have one
		if (substr($url, -1) != '/' and (($suffix = strrchr($url, '.')) === false or strlen($suffix) > 5))
		{
			\Config::get('url_suffix') and $url .= \Config::get('url_suffix');
		}

		if ( ! empty($get_variables))
		{
			$char = strpos($url, '?') === false ? '?' : '&';
			if (is_string($get_variables))
			{
				$url .= $char.str_replace('%3A', ':', $get_variables);
			}
			else
			{
				$url .= $char.str_replace('%3A', ':', http_build_query($get_variables));
			}
		}

		array_walk(
			$variables,
			function ($val, $key) use (&$url)
			{
				$url = str_replace(':'.$key, $val, $url);
			}
		);

		is_bool($secure) and $url = http_build_url($url, array('scheme' => $secure ? 'https' : 'http'));

		return $url;
	}

	/**
	 * Builds a query string by merging all array and string values passed. If
	 * a string is passed, it will be assumed to be a switch, and converted
	 * to "string=1".
	 *
	 * @param array|string Array or string to merge
	 * @param array|string ...
	 *
	 * @return string
	 *
	 * @since  1.7.0
	 */
	public static function buildQueryString()
	{
		$params = array();

		foreach (func_get_args() as $arg)
		{
			$arg = is_array($arg) ? $arg : array($arg => '1');

			$params = array_merge($params, $arg);
		}

		return http_build_query($params);
	}

	/**
	 * Gets the base URL, including the index_file if wanted.
	 *
	 * @param   bool    $include_index  Whether to include index.php in the URL
	 *
	 * @return  string
	 *
	 * @since  1.0.0
	 */
	public static function base($include_index = true)
	{
		$url = \Config::get('base_url');

		if ($include_index and \Config::get('index_file'))
		{
			$url .= \Config::get('index_file').'/';
		}

		return $url;
	}

	/**
	 * Get the object instance for this Facade
	 *
	 * @returns	Uri
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		// get the current request instance
		if ($request = \Request::getInstance())
		{
			return $request->getUri();
		}

		// no active request
		return null;
	}
}
