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

use Fuel\Foundation\Application as App;

/**
 * ViewManager Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class ViewManager extends Base
{
	/**
	 * @var  array  List of loaded view managers
	 *
	 * @since  2.0.0
	 */
	protected static $managers = array();

	/**
	 * Create a new view manager instance
	 *
	 * @param  Application  $app  Application object on which to forge this view manager
	 * @throws InvalidArgumentException if the view manager is already forged
	 * @returns	\Fuel\Display\ViewManager
	 *
	 * @since  2.0.0
	 */
	public static function forge(App $app)
	{
		// do we already have this view manager?
		if (isset(static::$managers[$app->getName()]))
		{
			throw new \InvalidArgumentException('The view manager "'.$app->getName().'" is already forged.');
		}

		return static::$managers[$app->getName()] = \Dependency::resolve('view', array(
			\Dependency::resolve('finder', array(
				array($app->getPath()),
			)),
			array(
				'cache' => $app->getPath().'cache',
			)
		));
	}

	/**
	 * Get a defined application instance
	 *
	 * @param  $name  name of the application
	 * @throws InvalidArgumentException if the requested application does not exist
	 * @returns	Application
	 *
	 * @since  2.0.0
	 */
	public static function get($name)
	{
		if ( ! isset(static::$managers[$name]))
		{
			throw new \InvalidArgumentException('There is no view manager defined named "'.$name.'".');
		}

		return static::$managers[$name];
	}

	/**
	 * Get the object instance for this Facade
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		return null;
	}
}
