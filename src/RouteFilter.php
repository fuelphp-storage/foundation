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
	 * @var  string  The app instance
	 */
	protected $app;

	/**
	 * @var  string  The type of controller to resolve
	 */
	protected $type = 'Controller';

	/**
	 * Constructor
	 * the segments.
	 *
	 * @param   Application  The application object
	 * @return  void
	 */
	public function __construct($app)
	{
		$this->app = $app;
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
			// find a match
			foreach ($this->app->getNamespaces() as $namespace)
			{
			// skip non-routeable namespaces
				if ( ! $namespace['routeable'] and $this->app->factory->isMainRequest())
				{
					continue;
				}

				// skip if we don't have a prefix match
				if ($namespace['prefix'] and strpos($match->translation, $namespace['prefix']) !== 0)
				{
					continue;
				}

				$match->setNamespace($namespace['namespace']);

				// get the segments from the translated route
				$segments = explode('/', ltrim(substr($match->translation, strlen($namespace['prefix'])),'/'));

				$arguments = array();
				while(count($segments))
				{
					$class = $match->namespace.$this->type.'\\'.implode('\\', array_map('ucfirst', $segments));
					if (class_exists($class))
					{
						$match->path = $namespace['path'];
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
