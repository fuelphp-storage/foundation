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

use Fuel\Dependency\ResolveException;

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
	 * @since  1.0.
	 */
	public function __construct($app)
	{
		$this->app = $app;
	}

	/**
	 * DUMMY ROUTE METHOD, maps directly from uri to Controller
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
		// do a straight URI-2-Controller mapping, so we can get on with things...
		$segments = explode('/', trim($uri, '/'));
		empty($segments[0]) and $segments = array('welcome');
		count($segments) == 1 and $segments[] = 'index';

		$controller = ucfirst(array_shift($segments));
		$segments[0] = ucfirst($segments[0]);

		try
		{
			$controller = \Dependency::resolve('\Controller\\'.$controller);
		}
		catch (ResolveException $e)
		{
			// for now, our fixed 404 method
			$controller = \Dependency::resolve('\Controller\Welcome');
			$segments[0] = 'error404';
		}

		return array(
			$controller,				// matched controller object
			$segments,					// segments list
			array('name' => 'John'),	// named parameters in the route
		);
	}

}
