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

class ApplicationServicesProviderTest extends Test
{

	public function testConstructRequest()
	{
		$this->assertInstanceOf(
			'Fuel\Foundation\Request\Cli',
			Application::init([])->getDependencyContainer()->get('fuel.application.request')
		);
	}

	public function testConstructResponse()
	{
		$this->assertInstanceOf(
			'Fuel\Foundation\Response\Cli',
			Application::init([])->getDependencyContainer()->get('fuel.application.response')
		);
	}
}
