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
 * Component Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Component extends Base
{
	/**
	 * Get the object instance for this Facade
	 *
	 * @return  Component
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		// get the current requests' application object
		$stack = static::getDic()->resolve('requeststack');
		if ($request = $stack->top())
		{
			$component = $request->getComponent();
		}
		else
		{
			// fall back to the main component
			$component = static::getDic()->resolve('application::__main')->getComponent();
		}

		return $component;
	}
}
