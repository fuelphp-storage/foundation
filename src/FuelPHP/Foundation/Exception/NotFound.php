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
 * Not Found Exception
 *
 * Exception thrown when Requested resource doesn't exist.
 *
 * @package  FuelPHP\Foundation
 *
 * @since  1.1.0
 */
class NotFound extends Base
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
		$code === 0 and $code = 404;
		parent::__construct($message, $code, $previous);
	}
}
