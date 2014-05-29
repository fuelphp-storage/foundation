<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation\Response;

/**
 * FuelPHP Json response class
 *
 * Standardized response on any request initiated
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Json extends Base
{
	/**
	 * @var  string  mime type of the return body
	 */
	protected $contentType = 'application/json';

	/**
	 * Send the content to the output
	 *
	 * @return  Response
	 *
	 * @since  2.0.0
	 */
	public function sendContent()
	{
		echo $this->__toString();

		return $this;
	}

	/**
	 * Returns the body as a string.
	 *
	 * @return  string
	 *
	 * @since  1.0.0
	 */
	public function __toString()
	{
		$content = $this->content;

		if (is_object($content) and is_callable(array($content, '__toString')))
		{
			$content = (string) $content;
		}

		if ( ! is_array($content) and ! is_object($content))
		{
			$type = gettype($content);
			if ( ! $content = json_decode($content))
			{
				$content = array('ERROR' => 'Data type \''.$type.'\', passed to the JSON Response, was not recognized as valid JSON.');
			}
		}

		return $this->format->toJson($content);
	}
}
