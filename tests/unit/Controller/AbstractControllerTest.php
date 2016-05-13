<?php
/**
 * @package    Fuel\Foundation\Test
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2016 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation\Test\Controller;

use Codeception\TestCase\Test;
use Fuel\Foundation\Test\Stubs\Controller\AbstractControllerStub;

class AbstractControllerTest extends Test
{
	public function testParams()
	{
		$controller = new AbstractControllerStub();

		$this->assertNull($controller->getRouteParam('not here'));
		$this->assertEquals('foobar', $controller->getRouteParam('not here', 'foobar'));

		$controller->setRouteParams(['baz' => 'bat']);
		$this->assertEquals('bat', $controller->getRouteParam('baz'));
		$this->assertEquals(['baz' => 'bat'], $controller->getRouteParams());
	}
}
