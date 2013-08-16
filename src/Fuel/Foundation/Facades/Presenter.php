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
 * Presenter Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Presenter extends Base
{
	/**
	 * Factory for fetching the Presenter
	 *
	 * @param   string  $view  View string with also maps to the presenter class
	 * @param   string  $method Method to execute on render
	 * @param   bool  $autoFilter  whether or not we want to auto-filter variables
	 * @param   string  $view  Alterative view string, in case it's different from the Presenter used
	 *
	 * @throws  RuntimeException if the the presenter class could not be loaded
	 * @return  \Fuel\Display\Presenter
	 */
	public static function forge($uri, $method = 'view', $autoFilter = null, $view = null)
	{
		// was a custom view string passed?
		if ($view === null)
		{
			$view = $uri;
		}

		// get the active namespace list
		$namespaces = \Request::getInstance()->getApplication()->getNamespaces();

		// prepend the current namespace, we'll check that first
		$currentNamespace = \Request::getInstance()->getRoute()->namespace;

		// find the matching presenter
		$presenter = null;
		foreach ($namespaces as $namespace)
		{
			// normalize the namespace
			$namespace['namespace'] = trim($namespace['namespace'], '\\').'\\';

			// get the segments from the presenter string passed
			$segments = explode('/', $uri);
			while(count($segments))
			{
				$class = $namespace['namespace'].'Presenter\\'.implode('\\', array_map('ucfirst', $segments));

				if ( ! class_exists($class, false))
				{
					$file = $namespace['path'].'classes'.DS.'Presenter'.DS.implode('/', array_map('ucfirst', $segments)).'.php';
					if (file_exists($file))
					{
						include $file;
					}
				}

				if (class_exists($class))
				{
					$presenter = new $class(\View::getInstance(), $method, $autoFilter, $view);
					break;
				}

				array_pop($segments);
			}

			if ($presenter)
			{
				break;
			}
		}

		// bail out if the presenter class could not be loaded
		if ( ! is_object($presenter))
		{
			throw new \RuntimeException('Presenter class identified by "'.$uri.'" could not be found.');
		}

		// or is not a valid Presenter
		elseif ( ! $presenter instanceOf \Fuel\Display\Presenter)
		{
			throw new \RuntimeException('Presenter class "'.get_class($presenter).'" does not extend "Fuel\Display\Presenter".');
		}

		return $presenter;
	}

}
