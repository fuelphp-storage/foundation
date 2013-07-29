<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation\Security\Filter;

/**
 * HTML entities Security filter class
 *
 * Uses htmlentities() to encode variables for safer output.
 *
 * @package  Fuel\Foundation
 *
 * @since    2.0.0
 */
class HtmlEntities extends Base
{
	/**
	 * @param string $input the variable to be cleaned by escaping HTML entities
	 *
	 * @return string
	 */
	protected function cleanString($input)
	{
		return htmlentities(
			$input,
			$this->app->getConfig()->get('security.htmlentities_flags', ENT_QUOTES),
			$this->app->getEnvironment()->encoding,
			$this->app->getConfig()->get('security.htmlentities_double_encode', false)
		);
	}
}
