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
use Fuel\Foundation\Formatter\Noop;
use Fuel\Foundation\Response\Http as HttpResponse;

class NoopTest extends Test
{
	public function testCanActivate()
	{
		$noop = new Noop();

		$this->assertTrue($noop->canActivate(null));
	}

	public function testFormat()
	{
		$noop = new Noop();

		$response = new HttpResponse();

		$container = new Container();
		$container->add('fuel.application.response', $response);
		$noop->setContainer($container);

		$result = $noop->format('foobar');

		$this->assertEquals(
			'foobar',
			(string) $result->getBody()
		);
	}
}
