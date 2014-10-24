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
 * Response Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Response extends Base
{
	/**
	 * Creates an instance of the Response class
	 *
	 * @param   string  $body    The response body
	 * @param   int     $status  The HTTP response status for this response
	 * @param   array   $headers Array of HTTP headers for this reponse
	 *
	 * @return  Response
	 */
	public static function forge($type)
	{
		$args = func_get_args();
		array_shift($args);

		return static::getDic()->resolve('response.'.$type, $args);
	}

}
