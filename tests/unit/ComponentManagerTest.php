<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2017 Fuel Development Team
 * @link       http://fuelphp.com
 */

declare(strict_types=1);

namespace Fuel\Foundation\Test;

use Codeception\TestCase\Test;
use Fuel\FileSystem\Finder;
use Fuel\Foundation\ComponentManager;

class ComponentManagerTest extends Test
{
	/**
	 * @var ComponentManager
	 */
	protected $manager;

	protected function setUp()
	{
		parent::setUp();
		$this->manager =  new ComponentManager(new Finder());
	}

	public function testLoading()
	{
		$this->assertFalse($this->manager->loaded('Basic'));

		$component = $this->manager->load('Basic');

		$this->assertInstanceOf(
			'Basic\FuelComponent',
			$component
		);

		$this->assertTrue($this->manager->loaded('Basic'));

		$this->manager->unload('Basic');

		$this->assertFalse($this->manager->loaded('Basic'));
	}

	public function testGet()
	{
		$this->assertFalse($this->manager->loaded('Basic'));

		$component = $this->manager->get('Basic');

		$this->assertInstanceOf(
			'Basic\FuelComponent',
			$component
		);

		$this->assertTrue($this->manager->loaded('Basic'));

		$this->assertTrue($component === $this->manager->get('Basic'));
	}

	/**
	 * @expectedException \Fuel\Foundation\Exception\ComponentLoad
	 * @expectedExceptionMessage FOU-001: Unable to load [Foobar\FuelComponent]: Class not found
	 */
	public function testLoadMissing()
	{
		$this->manager->load('Foobar');
	}

	/**
	 * @expectedException \Fuel\Foundation\Exception\ComponentLoad
	 * @expectedExceptionMessage FOU-002: Unable to load [Broken\FuelComponent]: Does not implement ComponentInterface
	 */
	public function testLoadMissingInterface()
	{
		$this->manager->load('Broken');
	}
}
