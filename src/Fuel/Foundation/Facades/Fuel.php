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
 * Fuel Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  1.0.0
 */
class Fuel extends Base
{
	/**
	 * @var  string  The global version of framework
	 *
	 * @since  1.0.0
	 */
	const VERSION = '2.0-dev';

	/**
	 * Get the object instance for this Facade
	 *
	 * @since  2.0.0
	 */
	protected static function getInstance()
	{
		return null;
	}
}
