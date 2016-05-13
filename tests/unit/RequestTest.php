<?php
/**
 * @package    Fuel\Foundation\Test
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2016 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation\Test;

use Codeception\TestCase\Test;
use Fuel\Foundation\Application;
use Fuel\Foundation\Request\Http as HttpRequest;

/**
 * Collection of tests to test various request handling
 */
class RequestTest extends Test
{
	public function testMakeRequestWithParams()
	{
		$app = Application::init([
			'components' => [
				'Basic',
			],
		]);

		$request = new HttpRequest([], [], '/params/foobar');
		$response = $app->performRequest($request);

		$this->assertEquals(200, $response->getStatusCode());
		$this->assertEquals('got: foobar', (string) $response->getBody());
	}
}
