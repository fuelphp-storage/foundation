<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation;

/**
 * FuelPHP Router class
 *
 * Converts a URI to a callable class method
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class Router
{
	/**
	 * @var  Application  app that created this router instance
	 *
	 * @since  2.0.0
	 */
	protected $app;

	/**
	 * Constructor
	 *
	 * @param  string  $resource
	 * @param  array|Input  $input
	 *
	 * @since  1.0.0
	 */
	public function __construct($app)
	{
		$this->app = $app;
	}

	/**
	 * DUMMY ROUTE METHOD, routes always to Welcome::index
	 *
	 * @param   string  $uri
	 *
	 * @throws  Exception\NotFound
	 *
	 * @return  array
	 *
	 * @since  2.0.0
	 */
	public function route($uri)
	{
		// return a dummy route match, so we can test request/response
		return array(
			\Fuel::resolve('\Controller\Welcome'),	// matched controller object
			array('index', 'param1', 'param2'),		// segments list
			array('param3' => 'param3'),			// named parameters in the route
		);
	}

}
