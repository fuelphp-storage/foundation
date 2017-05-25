<?php
/**
 * @package    Fuel\Foundation\Test
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2017 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation\Test;

use Codeception\TestCase\Test;
use Fuel\Dependency\Container;
use Fuel\Foundation\ResponseFormatter;
use Mockery;

class ResponseFormatterTest extends Test
{
	/**
	 * @var Container
	 */
	protected $dependencyContainer;

	protected function setUp()
	{
		parent::setUp();

		$this->dependencyContainer = new Container();
	}

	public function testConstruct()
	{
		$this->dependencyContainer->add('fuel.response.formatter.noop', 'Fuel\Foundation\Formatter\Noop');

		$instance = new ResponseFormatter(['fuel.response.formatter.noop'], $this->dependencyContainer);

		$this->assertInstanceOf(
			'Fuel\Foundation\Formatter\Noop',
			$instance->getFormatters()['fuel.response.formatter.noop']);
	}

	/**
	 * @expectedException \Fuel\Foundation\Exception\FormatterLoad
	 * @expectedExceptionMessage FOU-003: Unable to load [fuel.response.formatter.noop]: Does not implement FormatterInterface
	 */
	public function testConstructWithInvalidFormatter()
	{
		$this->dependencyContainer->add('fuel.response.formatter.noop', 'stdClass');

		new ResponseFormatter(['fuel.response.formatter.noop'], $this->dependencyContainer);
	}

	public function testCanFormatNegative()
	{
		$instance = new ResponseFormatter([], $this->dependencyContainer);
		$this->assertNull($instance->getFormatter([]));
	}

	public function testCanFormatPositive()
	{
		$this->dependencyContainer->add('fuel.response.formatter.noop', 'Fuel\Foundation\Formatter\Noop');

		$instance = new ResponseFormatter(['fuel.response.formatter.noop'], $this->dependencyContainer);

		$this->assertInstanceOf(
			'Fuel\Foundation\Formatter\Noop',
			$instance->getFormatter([])
		);
	}

	/**
	 * @expectedException  \Fuel\Foundation\Exception\Formatter
	 * @expectedExceptionMessage FOU-004: No formatter could be found
	 */
	public function testFormatWithNoFormatters()
	{
		$instance = new ResponseFormatter([], $this->dependencyContainer);
		$instance->format([]);
	}

	public function testFormat()
	{
		/** @var \Mockery\Mock $formatterMock */
		$formatterMock = Mockery::mock('\Fuel\Foundation\Formatter\FormatterInterface');

		$formatterMock
			->shouldReceive('setContainer')
			->with($this->dependencyContainer)
			->once();

		$formatterMock
			->shouldReceive('canActivate')
			->with(['foo' => 'bar'])
			->once()
			->andReturn(true);

		$formatterMock
			->shouldReceive('format')
			->with(['foo' => 'bar'])
			->once();

		$this->dependencyContainer->add('formatter.test', $formatterMock);

		$instance = new ResponseFormatter(['formatter.test'], $this->dependencyContainer);
		$instance->format(['foo' => 'bar']);
	}
}
