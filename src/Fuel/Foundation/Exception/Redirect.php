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
 * Redirect Exception
 *
 * Instances of classes that implement this can be returned as a valid Response
 * object from a Controller.
 *
 * @package  Fuel\Foundation
 *
 * @since  1.0.0
 */
class Redirect extends Exception
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
		$code === 0 and $code = 302;
		parent::__construct($message, $code, $previous);
	}

	/**
	 * Returns the location to which to redirect
	 *
	 * @return  string
	 *
	 * @since  2.0.0
	 */
	public function getLocation()
	{
		return $this->getMessage();
	}

	/**
	 * Returns the HTTP status with which to redirect
	 *
	 * @return int
	 */
	public function getStatus()
	{
		return $this->getCode();
	}

	/**
	 * Forges a Response object belonging to this Redirect
	 *
	 * @param   \Fuel\Foundation\Application  $app
	 * @return  \Fuel\Foundation\Response
	 */
	public function response(Application $app)
	{
die('Exception\Redirect not implemented yet');
		$response = $app->forge('Response.redirect', null, $this->getStatus());

		$location = $this->getLocation();
		strpos($location, '://') === false and $location = $app->env->baseUrl.$location;

		$response->setHeader('Location', $location);
		return $response;
	}
}
