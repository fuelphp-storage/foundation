<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2015 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation\Response;

use Fuel\Common\Format;

/**
 * Accepts a Format instance
 */
trait FormatAware
{
	/**
	 * @var Format
	 */
	protected $format;

	/**
	 * Sets the Format instance
	 *
	 * @param Format $format
	 */
	public function setFormat(Format $format)
	{
		$this->format = $format;
	}
}
