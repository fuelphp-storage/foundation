<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation\Facades;

/**
 * View Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class View extends Base
{
	/**
	 * Sets a global variable, except that the variable will be
	 * accessible to all views and presenters.
	 *
	 *     View::setGlobal($name, $value);
	 *
	 * @param   string  variable name or an array of variables
	 * @param   mixed   value
	 * @param   bool    whether to filter the data or not
	 * @return  void
	 */
	public static function setGlobal($key, $value = null, $filter = null)
	{
		return static::getInstance()->set($key, $value, $filter);
	}

	/**
	 * Assigns a global variable by reference, except that the variable
	 * will be accessible to all views and presenters
	 *
	 *     View::bindGlobal($key, $value);
	 *
	 * @param   string  variable name
	 * @param   mixed   referenced variable
	 * @param   bool    whether to filter the data or not
	 * @return  void
	 */
	public static function bindGlobal($key, &$value, $filter = null)
	{
		return static::getInstance()->bind($key, $value, $filter);
	}

	/**
	 * Get the active applications View Manager
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		return \Application::getInstance()->getViewManager();
	}
}
