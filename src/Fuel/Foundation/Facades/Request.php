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
 * Request Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  1.0.0
 */
class Request extends Base
{
	/**
	 * Forge a new environment object
	 *
	 * @param  Application  $app  Application object on which to forge this environment
	 * @param  string  $enviroment  Name of the current environment
	 *
	 * @since  2.0.0
	 */
	public static function forge(App $app, $uri, Array $input = array())
	{
		return \Dependency::resolve('request', array($app, $uri, $input));
	}

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
