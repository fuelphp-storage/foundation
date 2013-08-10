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
 * Num Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Num extends Base
{
	/**
	 * @var  Fuel\Common\Num  singleton instance
	 */
	protected static $instance;

	/**
	 * Get a new Num instance.
	 *
	 * @return  Fuel\Common\Num  new Num instance
	 */
	public static function forge(Array $config = array(), Array $byteUnits = array())
	{
		return \Dependency::resolve('num', array($config, $byteUnits));
	}

	/**
	 * Get the default instance for this Facade
	 *
	 * @return  Fuel\Common\Num
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		if ( ! static::$instance)
		{
			static::$instance = static::forge(\Config::load('num', true), \Lang::load('byteunits', true));
		}

		return static::$instance;
	}
}
