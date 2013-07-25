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
 * Error Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Error extends Base
{
	/**
	 * @var  Whoops\Run
	 *
	 * @since  2.0.0
	 */
	protected static $errorHandler;

	/**
	 * Set the Error handler
	 *
	 * @since  2.0.0
	 */
	public static function setHandler($handler = null)
	{
		if ($handler === null)
		{
			// use the framework default Whoops error handler
			static::$errorHandler = new \Whoops\Run;
			static::$errorHandler->pushHandler(new \Whoops\Handler\PrettyPageHandler);

			static::$errorHandler->register();
		}
		else
		{
			// set a custom handler
			static::$errorHandler = $handler;
		}
	}

	/**
	 * Get the object instance for this Facade
	 *
	 * @since  2.0.0
	 */
	protected static function getInstance()
	{
		return static::$errorHandler;
	}
}
