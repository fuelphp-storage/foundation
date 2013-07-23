<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation\Security\String;

/**
 * HTML entities String Security class
 *
 * Uses htmlentities() to encode strings for safer output.
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Htmlentities extends Base
{
	protected function secure($input)
	{
		return htmlentities($input, ENT_QUOTES, $this->app->environment->encoding, false);
	}
}
