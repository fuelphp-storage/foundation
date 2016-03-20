<?php
/**
 * @package    Fuel\Foundation\Test
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2016 Fuel Development Team
 * @link       http://fuelphp.com
 */

declare(strict_types=1);

namespace Fuel\Foundation\Test;

use Codeception\TestCase\Test;
use Fuel\Foundation\Application;

class ApplicationTest extends Test
{

	public function testInit()
	{
		$app = Application::init([]);

		$this->assertInstanceOf(
			'Fuel\Foundation\Application',
			$app->getDependencyContainer()->get('fuel.application')
		);

		$this->assertInstanceOf(
			'Fuel\Event\Container',
			$app->getDependencyContainer()->get('fuel.application.event')
		);
	}

	public function testDiOverride()
	{
		$app = Application::init([
			'di' => [
				'fuel.application.event' => 'stdClass',
			],
		]);

		$this->assertInstanceOf(
			'stdClass',
			$app->getDependencyContainer()->get('fuel.application.event')
		);
	}

}
