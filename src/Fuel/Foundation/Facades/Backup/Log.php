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

use Monolog\Logger;
use Psr\Log\LoggerInterface;

/**
 * Log Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */

class Log extends Base
{
	/**
	 * Create a new Monolog Logger instance.
	 *
	 * @param  $name  name of the log instance
	 *
	 * @return  Logger  new Monolog instance
	 */
	public static function forge($name)
	{
		return \Dependency::multiton('log', $name, array($name));
	}

	/**
	 * Get the object instance for this Facade
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		// get the current environment via the active request instance
		if ($request = \Request::getInstance())
		{
			return $request->getApplication()->getLog();
		}

		return null;
	}
}
