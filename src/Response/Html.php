<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation\Response;

/**
 * FuelPHP HTMl response class
 *
 * Standardized response on any request initiated
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Html extends Base
{
	/**
	 * @var  string  mime type of the return body
	 */
	protected $contentType = 'text/html';

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
		// special treatment for integers and floats
		if (is_numeric($this->content))
		{
			$content = (string) $this->content;
		}

		// objects with a toString method
		elseif (is_object($this->content) and is_callable(array($this->content, '__toString')))
		{
			$content = (string) $this->content;
		}

		// and all other non-string values
		elseif ( ! is_string($content = $this->content))
		{
			// TODO: debug code
			ob_start();
			var_dump($content);
			$content = html_entity_decode(ob_get_contents());
			ob_get_clean();
			echo '<hr />Controller didn\'t return a string value:';
		}

		return $content;
	}
}
