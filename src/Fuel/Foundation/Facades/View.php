<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation\Facades;

/**
 * View Facade class
 *
 * @package  Fuel\Foundation
 *
 * @since  2.0.0
 */
class View extends Base
{

	/**
	 * Get the active applications View Manager
	 *
	 * @since  2.0.0
	 */
	public static function getInstance()
	{
		if ($app = \Application::getActive())
		{
			return \Dependency::multiton('viewmanager', $app->getName());
		}
		else
		{
			return null;
		}
	}
}
