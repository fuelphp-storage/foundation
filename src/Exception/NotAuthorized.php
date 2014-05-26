<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation\Exception;

use Exception;

/**
 * Not Authorized Exception
 *
 * Exception thrown when current user isn't authorized for Requested resource.
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class NotAuthorized extends Base
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
		$code === 0 and $code = 401;
		parent::__construct($message, $code, $previous);
	}
}
