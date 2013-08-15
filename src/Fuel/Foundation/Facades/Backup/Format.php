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
 * Format Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  1.0.0
 */
class Format extends Base
{
	/**
	 * Returns an instance of the Format object.
	 *
	 *     echo Format::forge(array('foo' => 'bar'))->toXml();
	 *
	 * @param   mixed  general date to be converted
	 * @param   string  data format the file was provided in
	 * @return  Format
	 */
	public static function forge($data = null, $from_type = null)
	{
		return \Dependency::resolve('format', array($data, $from_type, \Config::load('format', true) ?: array(), \Input::getInstance()));
	}

	/**
	 * Get the default instance for this Facade
	 *
	 * @return  Fuel\Event\Queue
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		return static::forge();
	}
}
