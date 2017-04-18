<?php
/**
 * @package    Fuel\Foundation\Test
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2017 Fuel Development Team
 * @link       http://fuelphp.com
 */

declare(strict_types=1);

namespace Fuel\Foundation\Test;

use Codeception\TestCase\Test;
use Fuel\Foundation\Application;
use Fuel\Foundation\Event\AppShutdown;
use Fuel\Foundation\Event\AppStarted;
use Fuel\Foundation\Event\RequestFinished;
use Fuel\Foundation\Event\RequestStarted;
use Fuel\Foundation\Event\ResponseFinished;
use Fuel\Foundation\Event\ResponseStarted;
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
		$this->assertSame($app, $event->getApplication());
	}

	public function testMakeRequest()
	{
		$requestStartCalled = false;
		$requestStartApplication = null;

		$requestEndCalled = false;
		$requestEndApplication = null;

		$app = Application::init([
			'components' => [
				'Basic',
			],
			'events' => [
				[
					'name' => 'fuel.request.started',
					'listener' => function (RequestStarted $requestStarted) use (&$requestStartCalled, &$requestStartApplication) {
						$requestStartCalled = true;
						$requestStartApplication = $requestStarted->getApplication();
					}
				],
				[
					'name' => 'fuel.request.finished',
					'listener' => function (RequestFinished $requestFinished) use (&$requestEndCalled, &$requestEndApplication) {
						$requestEndCalled = true;
						$requestEndApplication = $requestFinished->getApplication();
					}
				],
			],
		]);

		$request = new HttpRequest([], [], '/testroute');
		$response = $app->performRequest($request);

		$this->assertEquals(200, $response->getStatusCode());
		$this->assertEquals('found me', (string) $response->getBody());

		$this->assertTrue($requestStartCalled);
		$this->assertSame($app, $requestStartApplication);

		$this->assertTrue($requestEndCalled);
		$this->assertSame($app, $requestEndApplication);
	}

	public function testRun()
	{
		$responseStartCalled = false;
		$responseStartApplication = null;

		$responseEndCalled = false;
		$responseEndApplication = null;

		$appShutdownCalled = false;
		$appShutdownApplication = null;

		$app = Application::init([
			'components' => [
				'Basic',
			],
			'events' => [
				[
					'name' => 'fuel.response.started',
					'listener' => function (ResponseStarted $responseStarted) use (&$responseStartCalled, &$responseStartApplication) {
						$responseStartCalled = true;
						$responseStartApplication = $responseStarted->getApplication();
					}
				],
				[
					'name' => 'fuel.response.finished',
					'listener' => function (ResponseFinished $responseFinished) use (&$responseEndCalled, &$responseEndApplication) {
						$responseEndCalled = true;
						$responseEndApplication = $responseFinished->getApplication();
					}
				],
				[
					'name' => 'fuel.application.shutdown',
					'listener' => function (AppShutdown $appShutdown) use (&$appShutdownCalled, &$appShutdownApplication) {
						$appShutdownCalled = true;
						$appShutdownApplication = $appShutdown->getApplication();
					}
				],
			],
		]);

		// Set up a custom request and inject that
		$request = new HttpRequest([], [], '/testroute');
		$app->getDependencyContainer()->add('fuel.application.request', $request);

		$response = $app->run();

		// Fire off the request and see if the expected events are fired.
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertEquals('found me', (string) $response->getBody());

		$this->assertTrue($responseStartCalled);
		$this->assertSame($app, $responseStartApplication);

		$this->assertTrue($responseEndCalled);
		$this->assertSame($app, $responseEndApplication);

		$this->assertTrue($appShutdownCalled);
		$this->assertSame($app, $appShutdownApplication);
	}
}
