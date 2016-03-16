<?php
/**
 * @package    Fuel\Foundation\Test
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation\Test;

use Codeception\TestCase\Test;
use Fuel\Foundation\Application;

class ApplicationTest extends Test
{

	public function testExample()
	{
		$app = new Application();

		$this->assertTrue($app->returnTrue());
	}

}
