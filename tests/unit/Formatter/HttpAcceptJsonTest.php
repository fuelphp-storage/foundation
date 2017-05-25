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

namespace Fuel\Foundation\Test\Formatter;

use Codeception\TestCase\Test;
use Fuel\Dependency\Container;
use Fuel\Foundation\Formatter\HttpAcceptJson;
use Fuel\Foundation\Request\Http as HttpRequest;
use Fuel\Foundation\Request\Http;
use Fuel\Foundation\Response\Http as HttpResponse;

class HttpAcceptJsonTest extends Test
{
	public function testCanActivate()
	{
		$json = new HttpAcceptJson();

		$container = new Container();
		$json->setContainer($container);

		$container->add('fuel.application.request', new HttpRequest([], [], null, null, 'php://input', ['Accept' => 'application/json']));
		$container->add('fuel.application.response', new HttpResponse());

		$this->assertTrue($json->canActivate(null));

		$container->add('fuel.application.request', new HttpRequest([], [], null, null, 'php://input', ['Accept' => ['text/html', 'application/json']]));
		$container->add('fuel.application.response', new HttpResponse());

		$this->assertTrue($json->canActivate(null));

		$container->add('fuel.application.request', new HttpRequest([], [], null, null, 'php://input', ['Accept' => 'text/html']));
		$container->add('fuel.application.response', new HttpResponse());

		$this->assertFalse($json->canActivate(null));
	}

	public function testFormat()
	{
		$json = new HttpAcceptJson();

		$container = new Container();
		$json->setContainer($container);

		$container->add('fuel.application.request', new HttpRequest([], [], null, null, 'php://input', ['Accept' => 'application/json']));
		$container->add('fuel.application.response', new HttpResponse());

		$result = $json->format(['foo' => 'bar', 'true' => false]);

		$this->assertEquals(
			'{"foo":"bar","true":false}',
			(string) $result->getBody()
		);
	}
}
