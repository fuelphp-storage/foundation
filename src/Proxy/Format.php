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
	 * @return  \Fuel\Common\Format
	 *
	 * @since  2.0.0
	 */
	public static function forge()
	{
		return static::getDic()->get('format', func_get_args());
	}

	/**
	 * Get the default instance for this Facade
	 *
	 * @return  \Fuel\Common\Format
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		return static::forge();
	}
}
