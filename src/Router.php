<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation;

use Closure;
use Fuel\Routing\Match;
use Fuel\Foundation\Exception\NotFound;
use Fuel\Foundation\Exception\ServerError;

/**
 * Fuel Router class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Router extends \Fuel\Routing\Router
{
	/**
	 * @var  InjectionFactory  this applications object factory
	 *
	 * @since  2.0.0
	 */
	protected $factory;

	/**
	 * @var  Component  The component instance
	 */
	protected $component;

	/**
	 * @var  Fuel\Routing\Router  The router instance
	 */
	protected $routing;

	/**
	 * @var  string  The type of controller to resolve
	 */
	protected $namespacePrefix = 'Controller';

	/**
	 * @var  array  List of namespaces for which components are defined
	 */
	protected $namespaces = array();

	/**
	 * Constructor
	 * the segments.
	 *
	 * @param  Component            $component  The component object
	 * @param  InjectionFactory     $factory    factory object to construct external objects
	 */
	public function __construct($component, InjectionFactory $factory)
	{
		$this->component = $component;
		$this->factory = $factory;
	}

	/**
	 */
	public function setNamespacePrefix($namespacePrefix)
	{
		$this->namespacePrefix = ucfirst($namespacePrefix);

		return $this;
	}

	/**
	 * Fuel route resolver
	 */
	public function resolveRoute($uri, $method, $lastMatch = null)
	{
		// trim slashes
		$uri = trim($uri, '/');

		// check which component needs to process this uri, based on the component prefix
		$processors = array();
		foreach ($this->component->getApplication()->getComponents() as $prefix => $component)
		{
			// if we have a prefix match
			if (empty($prefix) or strpos($uri, $prefix) === 0)
			{
				// use the corresponding component
				array_unshift($processors, $component);
			}
		}

		// if no uri match is found, use the current components router
		if (empty($processors))
		{
			$processors[] = $this->component;
		}

		// resolve the route
		foreach ($processors as $component)
		{
			// do a route lookup
			$route = $component->getRouter()->translate($uri, $method);

			// was a route found, then bailout
			if ($route->route)
			{
				break;
			}
		}

		// if we don't have a controller or route exhaustion, recurse
		if ( ! $route->controller and $route->uri !== $route->translation)
		{
			// and do a recursive lookup
			$route = $this->resolveRoute($route->translation, $method, $route);
		}

		if (! $route->controller )
		{
			// use the last know good route
			$route = $lastMatch ?: $route;

			// is the route target a closure?
			if ($route->translation instanceOf Closure)
			{
				$route->controller = $route->translation;
			}
			else
			{
				// get the segments from the translated route
				$prefix = $component->getUri();

				if (empty($prefix) or strpos($route->translation, $prefix) === 0)
				{
					// strip the prefix from the uri
					$segments = explode('/', ltrim(substr($route->translation, strlen($prefix)), '/'));
				}
				else
				{
					$segments = explode('/', $route->translation);
				}

				$arguments = array();
				while(count($segments))
				{
					$class = $route->namespace.'\\'.implode('\\', array_map('ucfirst', $segments));
					if (class_exists($class))
					{
						$route->path = $component->getPath();
						$route->controller = $class;
						break;
					}
					array_unshift($arguments, array_pop($segments));
				}

				// any segments left?
				if ( ! empty($segments))
				{
					$route->action = ucfirst(array_shift($arguments));
				}

				// more? set them as additional segments
				$route->uri = implode('/', $arguments);
			}
		}

		return $route;
	}

	/**
	 * Resolve the route, and add the namespace and namespace prefix of this routers component
	 */
	public function translate($uri, $method)
	{
		// if we have a prefix match
		$prefix = $this->component->getUri();
		if (empty($prefix) or strpos($uri, $prefix) === 0)
		{
			// strip the prefix from the uri
			$uri = ltrim(substr($uri, strlen($prefix)), '/');
		}

		$route = parent::translate($uri, $method);

		$route->namespace = $this->component->getNamespace().'\\'.$this->namespacePrefix;

		return $route;
	}


	/**
	 * Fuel recusive reverse route fetching
	 */
	public function getRoute($name, $topToBottom = false)
	{
		// we need to do this from the top of the component tree
		if ($parent = $this->component->getParent() and $parent instanceOf Component)
		{
			return $parent->getRoute($name);
		}

		// now parent points to the application object, lets get going
		$components = $parent->getComponents();

		// check all of them for a route name match
		$route = null;
		foreach($components as $uri => $component)
		{
			if ($routes = $component->getRouter()->routes and isset($routes[$name]))
			{
				// store it
				$route = $routes[$name];

				// if higher-level components will
				if ($topToBottom)
				{
					// exit at the first hit found
					break;
				}
			}
		}

		return $route;
	}

	/**
	 */
	public function resolveController(Match $match)
	{
		// is the route target a closure?
		if ($match->translation instanceOf Closure)
		{
			$match->controller = $match->translation;
		}
		else
		{
			// fetch all components loaded by this application
			$components = $this->component->getApplication()->getComponents();

			// order them by namespace
			$namespaces = array();
			foreach ($components as $uri => $component)
			{
				// skip non-routeable components for the main request
				if ( ! $component->isRoutable() and $this->factory->isMainRequest())
				{
					continue;
				}

				$namespaces[$component->getNamespace()] = $component;
			}
			krsort($namespaces);

			// find a match
			foreach ($namespaces as $namespace => $component)
			{
				// skip if we don't have a prefix match
				if ($uri = $component->getUri() and strpos($match->translation, $uri) !== 0)
				{
					continue;
				}

				$match->setNamespace($namespace);

				// get the segments from the translated route
				$segments = explode('/', ltrim(substr($match->translation, strlen($uri)),'/'));

				$arguments = array();
				while(count($segments))
				{
					$class = $match->namespace.$this->namespacePrefix.'\\'.implode('\\', array_map('ucfirst', $segments));
					if (class_exists($class))
					{
						$match->path = $component->getPath();
						$match->controller = $class;
						break;
					}
					array_unshift($arguments, array_pop($segments));
				}

				// did we find a match
				if ($match->controller)
				{
					// then stop looking
					break;
				}
			}

			// any segments left?
			if ( ! empty($segments))
			{
				$match->action = ucfirst(array_shift($arguments));
			}

			// more? set them as additional segments
			$match->uri = implode('/', $arguments);
		}

		return $match;
	}

	/**
	 * Strip the component prefix from the URI passed
	 */
	protected function stripPrefix($uri)
	{
		$uri = trim($uri, '/');
		$componentUri = $this->component->getUri();
		if ($componentUri and strpos($uri, $componentUri) === 0)
		{
			$uri = substr($uri, strlen($componentUri)+1);
		}

		return $uri;
	}
}
