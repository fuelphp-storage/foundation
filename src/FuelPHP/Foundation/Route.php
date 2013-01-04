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

/**
 * Route container
 *
 * FuelPHP Route container class.
 *
 * @package  FuelPHP\Foundation
 *
 * @since  2.0.0
 */
class Route
{
	/**
	 * @var  \FuelPHP\Foundation\Environment
	 *
	 * @since  2.0.0
	 */
	protected $env;

	/**
	 * @var  \FuelPHP\Foundation\Application
	 *
	 * @since  2.0.0
	 */
	protected $app;

	/**
	 * @var  string  name of this route
	 *
	 * @since  1.1.0
	 */
	protected $name = '';

	/**
	 * @var  array  HTTP methods
	 *
	 * @since  1.1.0
	 */
	protected $methods = array();

	/**
	 * @var  string  uri this must match
	 *
	 * @since  1.1.0
	 */
	protected $search = '';

	/**
	 * @var  string  uri it translates to
	 *
	 * @since  1.1.0
	 */
	protected $translation = '';

	/**
	 * @var  array  uri segment variables
	 */
	protected $vars = array();

	/**
	 * @var  array  defaults used for reverse routing
	 */
	protected $varDefaults = array();

	/**
	 * @var  callback  something callable that matched
	 *
	 * @since  2.0.0
	 */
	protected $match;

	/**
	 * @var  array  URI segments
	 *
	 * @since  2.0.0
	 */
	protected $segments = array();

	/**
	 * @var  array
	 *
	 * @since  2.0.0
	 */
	protected $namedSegments = array();

	/**
	 * Constructor
	 *
	 * @param  string       $name
	 *
	 * @since  1.0.0
	 */
	public function __construct($name)
	{
		// set the environment variable necessary for the package loader object
		$this->env = \FuelPHP\Foundation\Environment::singleton();
		$this->app = $this->env->getActiveApplication();

		$this->name = $name;
	}

	/**
	 * Get the name of this route
	 *
	 * @return  string
	 *
	 * @since  1.0.0
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Define the search string to be matched for this route
	 *
	 * @return  Route
	 *
	 * @since  1.0.0
	 */
	public function match($search)
	{
		// store the search string
		$this->search = $search;
		is_string($this->search) and $this->search = '/'.trim($this->search, '/ ');

		// if no translation is present, default to the search string
		empty($this->translation) and $this->to($this->search);

		return $this;
	}

	/**
	 * Define the translated route to return on a match
	 *
	 * @return  Route
	 *
	 * @since  1.0.0
	 */
	public function to($translation)
	{
		// store the translation string
		$this->translation = is_null($translation) ? $this->search : $translation;
		if (is_string($this->translation))
		{
			$this->translation = '/'.trim($this->translation, '/ ');
		}

		return $this;
	}

	/**
	 * Set the methods this route acts on
	 *
	 * @param   array|string|...  $method
	 * @return  Route
	 *
	 * @since  2.0.0
	 */
	public function methods()
	{
		// reset the methods defined
		$this->methods = array();

		// loop over the methods passed
		foreach (func_get_args() as $method)
		{
			if (is_array($method))
			{
				$this->methods = array_merge($this->methods, $method);
			}
			else
			{
				$this->methods[] = $method;
			}
		}

		return $this;
	}

	/**
	 * Set named variables to retrieve from the URI, optionally with default values for reverse routing
	 *
	 * @param   string             $var
	 * @param   null|string|array  $regex  null for segment regex, string regex or array(regex, default)
	 * @return  Fuel
	 */
	public function setVar($var, $regex = null)
	{
		! is_array($var) and $var = array($var => $regex);

		foreach ($var as $name => $regex)
		{
			// Check if a default value is provided for reverse routing
			if (is_array($regex))
			{
				list($regex, $default) = $regex;
				$this->varDefaults[$name] = $default;
			}

			// Use the segment matching regex if none is given
			is_null($regex) and $regex = '[^/]+';

			$this->vars[$name] = $regex;
		}

		return $this;
	}

	/**
	 * Fetch a URI for reverse routing
	 *
	 * @param   array  $vars
	 * @return  string
	 * @throws  \UnexpectedValueException
	 */
	public function get(array $vars = array())
	{
		// Non string uris are returned unmodified
		if ( ! is_string($this->search))
		{
			throw new \UnexpectedValueException('Reverse routing is not possible with Closures.');
		}

		// Get the route and replace the variables
		$route = $this->search;
		foreach ($this->vars as $var => $regex)
		{
			$val = array_key_exists($var, $vars)
				? $vars[$var]
				: (array_key_exists($var, $this->varDefaults) ? $this->varDefaults[$var] : '');
			$route = str_replace('{'.$var.'}', $val, $route);
		}

		return $route;
	}

	/**
	 * Checks if the uri matches this route
	 *
	 * @param   string  $uri
	 * @return  bool    whether it matched
	 *
	 * @since  2.0.0
	 */
	public function matches($uri)
	{
		$request = $this->app->getActiveRequest();
		if ( ! empty($this->methods)
			and ! in_array(strtoupper($request->input->getMethod()), $this->methods))
		{
			return false;
		}

		if ($this->search instanceof \Closure)
		{
			// Given translation is superseded by the callback output when not just boolean true
			$translation = call_user_func($this->search, $uri, $this->app, $request);
			$translation === true and $translation = $this->translation;

			if ($translation)
			{
				return $this->parse($translation);
			}
		}
		elseif (is_string($this->search))
		{
			$search = $this->vars ? $this->compileSearch($uri) : $this->search;

			$translation = preg_replace('#^'.$search.'$#uD', $this->translation, $uri, -1, $count);
			if ($count)
			{
				return $this->parse($translation);
			}
		}

		// Failure...
		return false;
	}

	/**
	 * Return an array with 1. callable to be the controller and 2. additional params array
	 * and 3. associative array with the named parameters
	 *
	 * @return  array  callback, segments, named_segments
	 *
	 * @since  2.0.0
	 */
	public function getMatch()
	{
		return array($this->match, $this->segments, $this->namedSegments);
	}

	/**
	 * Adds in the regexes for URI variables
	 *
	 * @param   string  $uri  for finding the uri params
	 * @return  Closure|mixed|string
	 *
	 * @since  2.0.0
	 */
	protected function compileSearch($uri)
	{
		$search  = $this->search;
		$match   = $this->search;
		foreach ($this->vars as $name => $regex)
		{
			$search  = str_replace('{'.$name.'}', '('.$regex.')', $search);
			$match   = str_replace('{'.$name.'}', '(?P<'.$name.'>'.$regex.')', $search);
		}

		// Fetch the named segments from the URI
		preg_match($match, $uri, $matches);
		foreach ($matches as $k => $val)
		{
			is_string($k) and $this->namedSegments[$k] = $val;
		}

		return $search;
	}

	/**
	 * Attempts to find the controller and returns success
	 *
	 * @param   string  $translation
	 * @return  bool
	 *
	 * @since  1.1.0
	 */
	protected function parse($translation)
	{
		// Return directly if it's a Closure or a callable array
		if ($translation instanceof \Closure
			or (is_array($translation) and is_callable($translation)))
		{
			return true;
		}

		// Return Controller when found
		if (is_string($translation) and ($controller = $this->findClass($translation)))
		{
			$this->match = $this->env->forge($controller);
			return true;
		}

		// Failure...
		return false;
	}

	/**
	 * Parses the URI into a controller class
	 *
	 * @param   string  $uri
	 * @return  bool|string
	 *
	 * @since  2.0.0
	 */
	protected function findClass($uri)
	{
		$uriArray = explode('/', trim($uri, '/'));
		while ($uriArray)
		{
			$uri = implode('/', array_map(function($val) { return ucfirst(strtolower($val)); }, $uriArray));
			if ($controller = $this->app->findClass('Controller', $uri, true))
			{
				return $controller;
			}
			array_unshift($this->segments, array_pop($uriArray));
		}
		return false;
	}
}
