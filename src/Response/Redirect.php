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
 * FuelPHP Redirect response class
 *
 * Standardized response on any request initiated
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Redirect extends Base
{
	/**
	 * Constructor
	 *
	 * @param  string  $url
	 * @param  string  $method
	 * @param  int     $status
	 * @param  array   $headers
	 *
	 * @since  2.0.0
	 */
	public function __construct($app, $url = '', $method = 'location', $status = 200, array $headers = array())
	{
		parent::__construct($app, '', $status, $headers);

		if (strpos($url, '://') === false)
		{
			$url = $this->app->getEnvironment()->getBaseUrl().$url;
		}

		if ($method == 'location')
		{
			$this->setHeader('Location', $url);
		}
		elseif ($method == 'refresh')
		{
			$this->setHeader('Refresh', '0;url='.$url);
		}
		else
		{
			throw new \InvalidArgumentException('FOU-022: ['.$method.'] is not a valid redirect method.');
		}
	}

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
		// can't throw an Exception in a __toString(), so we'll have to do it like this...
		die('A redirect response does not have any content to display. Did you forget to return it?');
	}
}
