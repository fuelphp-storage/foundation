<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    FuelPHP\Foundation
 * @version    2.0
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 */

namespace FuelPHP\Foundation\Exception;

use Exception;

/**
 * Forbidden
 *
 * Exception thrown when Requested resource is never accessible in this way.
 *
 * @package  FuelPHP\Foundation
 *
 * @since  1.1.0
 */
class Forbidden extends Base
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
		$code === 0 and $code = 403;
		parent::__construct($message, $code, $previous);
	}
}
