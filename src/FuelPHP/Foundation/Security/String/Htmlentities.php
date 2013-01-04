<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    FuelPHP\Foundation
 * @version    2.0
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 */

namespace FuelPHP\Foundation\Security\String;

/**
 * HTML entities String Security class
 *
 * Uses htmlentities() to encode strings for safer output.
 *
 * @package  Fuel\Kernel
 *
 * @since  2.0.0
 */
class Htmlentities extends Base
{
	protected function secure($input)
	{
		return htmlentities($input, ENT_QUOTES, $this->env->encoding, false);
	}
}
