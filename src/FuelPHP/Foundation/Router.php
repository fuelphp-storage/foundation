<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    Fuel\Core
 * @version    2.0.0
 * @license    MIT License
 * @copyright  2010 - 2012 Fuel Development Team
 */

namespace FuelPHP\Foundation;

/**
 * Router class
 *
 * The Profiler class collects information about your application being run from various sources.
 *
 * @package  Fuel\Core
 *
 * @since  1.0.0
 */
class Router
{
	/**
	 * @var  Environment
	 *
	 * @since  2.0.0
	 */
	protected $env;

	/**
	 * @var  Application  app that created this request
	 *
	 * @since  2.0.0
	 */
	protected $app;

	/**
	 * @var  array  route objects
	 *
	 * @since  2.0.0
	 */
	protected $routes = array();

	/**
	 * Constructor
	 *
	 * @since  2.0.0
	 */
	public function __construct()
	{
		// set the environment variable necessary for the package loader object
		$this->env = \FuelPHP::resolve('Environment');
		$this->app = $this->env->getActiveApplication();

		// load the routes
		// scan through the loaded packages, and load all defined routes
		foreach ($this->app->getPackages() as $pkgs)
		{
			foreach ($pkgs as $pkg)
			{
				if ($pkg->getRoutable())
				{
					// load in the routes file
					$path = $pkg->getPath().'routes.php';
					file_exists($path) and require $path;
				}
			}
		}
	}

	/**
	 * Attempts to route a given URI to a controller (class, Closure or callback)
	 *
	 * @param   string  $uri
	 *
	 * @throws  Exception\NotFound
	 *
	 * @return  array
	 *
	 * @since  2.0.0
	 */
	public function route($uri)
	{
		// Attempt other routes
		foreach ($this->routes as $route)
		{
			if ($route->matches($uri))
			{
				return $route->getMatch();
			}
		}

		throw new Exception\NotFound($uri);
	}

	/**
	 * Add a route to the Application
	 *
	 * @param   string\Route     $name
	 * @param   null|int|string  $offset  null for at the end, int for position, or string for insert before named route
	 *
	 * @return  Route
	 *
	 * @since  2.0.0
	 */
	public function add($name = null, $offset = null)
	{
		if ( ! $name instanceof Route)
		{
			is_null($name) and $name = sha1(time());
			$route = \FuelPHP::resolve('Route', null, $name);
		}
		else
		{
			$route = $name;
			$name = $route->getName();
		}

		// Allow to insert into route stack at location of existing named route
		if (is_string($offset))
		{
			$offset = array_search($offset, array_keys($this->routes));
			! is_int($offset) and $offset = null;
		}

		if (isset($offset))
		{
			$this->routes = array_slice($this->routes, 0, $offset)
				+ array($name => $route)
				+ array_slice($this->routes, $offset);
		}
		else
		{
			$this->routes[$name] = $route;
		}

		return $this->routes[$name];
	}

	/**
	 * Reverse routing
	 *
	 * @param   string  $name
	 * @param   array   $vars
	 *
	 * @throws  \OutOfBoundsException
	 *
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function getRoute($name, array $vars = array())
	{
		if ( ! isset($this->routes[$name]))
		{
			throw new \OutOfBoundsException('Requesting an unregistered route.');
		}
		return $this->routes[$name]->get($vars);
	}

}
