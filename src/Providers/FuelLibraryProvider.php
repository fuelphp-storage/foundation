<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2015 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation\Providers;

use Fuel\Foundation\LibraryProvider;

/**
 * Fuel LibraryProvider class for Foundation
 */
class FuelLibraryProvider extends LibraryProvider
{
	/**
	 * {@inheritdoc}
	 */
	public function initialize()
	{
		// fetch the alias instance
		$alias = $this->container->get('alias');

		// alias the Fuel class to global
		$alias->alias('Fuel', 'Fuel\Foundation\Fuel');

		// alias the base controllers to the Fuel\Controller namespace
		$alias->aliasNamespace('Fuel\Foundation\Controller', 'Fuel\Controller');
	}
}
