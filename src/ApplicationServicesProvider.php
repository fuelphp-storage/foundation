<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2016 Fuel Development Team
 * @link       http://fuelphp.com
 */

declare(strict_types=1);

namespace Fuel\Foundation;

use League\Container\ServiceProvider;

class ApplicationServicesProvider extends ServiceProvider
{

	protected $provides = [
		'fuel.application.event',
	];

	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$this->getContainer()->add('fuel.application.event', 'Fuel\Event\Container');
	}
}
