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

use Application;
use Exception;

/**
 * Request Exception
 *
 * Base Exception to throw when a Request fails.
 *
 * @package  FuelPHP\Foundation
 *
 * @since  1.1.0
 */
class Base extends \RuntimeException
{
	/**
	 * @var  \FuelPHP\Foundation\Application
	 */
	protected $app;
}
