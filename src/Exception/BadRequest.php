<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation\Exception;

use Exception;

/**
 * Bad Request Exception
 *
 * Exception thrown when the request cannot be fulfilled due to bad syntax.
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class BadRequest extends Base
{
	/**
	* Extend constructor to default error code to HTTP code
	*
	* @param  string           $message
	* @param  int              $code
	* @param  \Exception|null  $previous
	*/
	public function __construct($message = '', $code = 0, Exception $previous = null)
	{
		$code === 0 and $code = 400;
		parent::__construct($message, $code, $previous);
	}
}
