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
use Fuel\Foundation\Event\AppStarted;
use Fuel\Foundation\Request\Http as HttpRequest;
use Zend\Diactoros\Request;

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
			'League\Event\Emitter',
			$app->getDependencyContainer()->get('fuel.application.event')
		);

		$this->assertInstanceOf(
			'Fuel\Foundation\ComponentManager',
			$app->getDependencyContainer()->get('fuel.application.component_manager')
		);
	}

	public function testEventRegister()
	{
		$called = false;
		$app = Application::init([
			'events' => [
				[
					'name' => 'foobar',
					'listener' => function () use (&$called) {
						$called = true;
					}
				],
			],
		]);

		$app->getDependencyContainer()
			->get('fuel.application.event')
			->emit('foobar');

		$this->assertTrue($called);
	}

	public function testLoadComponent()
	{
		$app = Application::init([
			'components' => [
				'Basic',
			],
		]);

		$this->assertTrue(
			$app->getDependencyContainer()
				->get('fuel.application.component_manager')
				->loaded('Basic')
		);

		$config = $app->getDependencyContainer()->get('fuel.config');
		$config->load('config');

		// check if the config has been loaded
		$this->assertEquals(
			'bar',
			$config->get('basic_config_foo')
		);
	}

	public function testAppCreatedEvent()
	{
		$called = false;

		/** @var AppStarted $event */
		$event = null;

		$app = Application::init([
			'events' => [
				[
					'name' => 'fuel.application.started',
					'listener' => function (AppStarted $appStarted) use (&$called, &$event) {
						$called = true;
						$event = $appStarted;
					}
				],
			],
		]);

		$this->assertTrue($called);
		$this->assertEquals($app, $event->getApplication());
	}

	public function testMakeRequest()
	{
		$app = Application::init([
			'components' => [
				'Basic',
			],
		]);

		$request = new HttpRequest([], [], '/testroute');
		$response = $app->performRequest($request);

		$this->assertEquals(200, $response->getStatusCode());
		$this->assertEquals('found me', (string) $response->getBody());
	}
}
