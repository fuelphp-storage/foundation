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
 * Base Security class
 *
 * Container for various Security handlers.
 *
 * @package  Fuel\Foundation
 *
 * @since    1.0.0
 */
class Security
{
	/**
	 * @var  Application  this objects application instance
	 *
	 * @since  2.0.0
	 */
	protected $app;

	/**
	 * @var  array  list of loaded security filters
	 *
	 * @since  2.0.0
	 */
	protected $filters = array();

	/**
	 * @var  array  list of cleaned variables
	 *
	 * @since  2.0.0
	 */
	protected $cleaned = array();

	/**
	 * Setup the application security object.
	 *
	 * @return  void
	 *
	 * @since  2.0.0
	 */
	public function __construct($app)
	{
		// store this app's instance
		$this->app = $app;

		// load the security configuration
		$this->app->getConfig()->load('security', true);
	}

	/**
	 * Cleans the request URI
	 *
	 * @param  string $uri     uri to clean
	 * @param  bool   $strict  whether to remove relative directories
	 */
	public function cleanUri($uri, $strict = false)
	{
		$filters = $this->app->getConfig()->get('security.uri_filter', array());
		$filters = is_array($filters) ? $filters : array($filters);

		if ($strict)
		{
			$uri = preg_replace(array("/\.+\//", '/\/+/'), '/', $uri);
		}

		return $this->clean($uri, $filters);
	}

	/**
	 * Generic variable clean method
	 *
	 * @param  mixed  $var     the variable to clean
	 * @param  mixed  $filters list of filters to apply to the variable (method names or callables)
	 * @param  string $type    default filter definition to apply if no filters are given
	 *
	 */
	public function clean($var, $filters = null, $type = 'security.input_filter')
	{
		// if no filters are given, load the defaults from config
		is_null($filters) and $filters = $this->app->getConfig()->get($type, array());

		// and make sure it's an array
		$filters = is_array($filters) ? $filters : array($filters);

		foreach ($filters as $filter)
		{
			// do we have this filter loaded? or can we load it?
			if (array_key_exists(strtolower($filter), $this->filters) or $this->loadFilter($filter))
			{
				$filter = $this->filters[strtolower($filter)];
			}

			// does the filter have a callable clean() method?
			if (is_callable(array($filter, 'clean')))
			{
				$var = $filter->clean($var);
			}

			// is the filter callable in itself?
			elseif (is_callable($filter))
			{
				$var = $filter($var);
			}

			// assume it's a regex of characters to filter
			else
			{
				$var = $this->filterRegex($var, $filter);
			}
		}

		return $var;
	}

	/**
	 * @param mixed $input variable to check
	 *
	 * @return bool, true if the variable was cleaned before
	 */
	public function isCleaned($input)
	{
		return in_array($input, $this->cleaned, true);
	}

	/**
	 * @param mixed $input a cleaned variable
	 */
	public function isClean($input)
	{
		$this->cleaned[] = $input;
	}

	/**
	 * @param string $filter name of the filter class to load
	 *
	 * @return bool
	 */
	protected function loadFilter($filter)
	{
		static $misses = array();

		if ( ! in_array($filter, $misses))
		{
			try
			{
				if ($obj = \Dependency::resolve('Fuel\Foundation\Security\Filter\\'.$filter, array($this->app, $this)))
				{
					$this->filters[strtolower($filter)] = $obj;

					return true;
				}
			}
			catch (\Fuel\Dependency\ResolveException $e)
			{
				// we don't have a class for this filter
				$misses[] = $filter;
			}
		}

		return false;
	}

	/**
	 * @param mixed  $var   the variable to filter
	 * @param string $filter  the regex to apply
	 *
	 * @return mixed
	 */
	protected function filterRegex($var, $filter)
	{
		if (is_array($var))
		{
			foreach($var as $key => $value)
			{
				$var[$key] = preg_replace('#['.$filter.']#ui', '', $value);
			}
		}
		else
		{
			$var = preg_replace('#['.$filter.']#ui', '', $var);
		}

		return $var;
	}
}
