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

use Fuel\Routing\Match;

/**
 * RouteFilter class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class RouteFilter
{
	/**
	 * @var  InjectionFactory  this applications object factory
	 *
	 * @since  2.0.0
	 */
	protected $factory;

	/**
	 * @var  string  The component instance
	 */
	protected $component;

	/**
	 * @var  string  The type of controller to resolve
	 */
	protected $type = 'Controller';

	/**
	 * Constructor
	 * the segments.
	 *
	 * @param  Component         $component  The component object
	 * @param  InjectionFactory  $factory    factory object to construct external objects
	 */
	public function __construct($component, InjectionFactory $factory)
	{
		$this->component = $component;

		// store the applications object factory
		$this->factory = $factory;
	}

	/**
	 */
	public function setType($type)
	{
		$this->type = ucfirst($type);

		return $this;
	}

	/**
	 */
	public function filter(Match $match)
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
					$class = $match->namespace.$this->type.'\\'.implode('\\', array_map('ucfirst', $segments));
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
		}

		// any segments left?
		if ( ! empty($segments))
		{
			$match->action = ucfirst(array_shift($arguments));
		}

		// more? set them as additional segments
		$match->uri = implode('/', $arguments);

		return $match;
	}

}
