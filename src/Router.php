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
	 * Fuel route translation
	 */
	public function translate($uri, $method)
	{
		// stack of processed URI's to detect routing loops
		$loopDetection = array();

		// recusively resolve the route
		$route = $this->recursiveTranslate($uri, $method);

		// loop until we have a controller or route exhaustion
		while ( ! $route->controller and $route->uri !== $route->translation)
		{
			// save the one we found
			$match = $route;

			// check if we've seen this uri before
			if (in_array($route->uri, $loopDetection))
			{
				$loopDetection[] = $route->uri;
				throw new ServerError('Recursive route detected: '.implode(' => ', $loopDetection));
			}
			$loopDetection[] = $route->uri;

			// recusively resolve the route
			$route = $this->recursiveTranslate($route->translation, $method);
		}

		// did the route resolve to a controller?
		if (isset($match) and empty($match->controller))
		{
			// no, see if we can find one dynamically
			$route = $this->resolveController($match);

			// still not found? Then bail out!
			if (empty($route->controller))
			{
				throw new NotFound('No route match has been found.');
			}
		}

		return $route;
	}

	/**
	 * Fuel recusive route translation
	 */
	public function recursiveTranslate($uri, $method, $reset = false)
	{
		// try to resolve it using the local routing instance
		$route = parent::translate($uri, $method);

		// if not found, try our parent
		if( ! $route->route and $parent = $this->component->getParent() and $parent instanceOf Component)
		{
			$route = $parent->getRouter()->recursiveTranslate($uri, $method);
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
}
