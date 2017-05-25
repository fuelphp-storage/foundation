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

namespace Fuel\Foundation;

use Fuel\Foundation\Exception\Formatter;
use Fuel\Foundation\Exception\FormatterLoad;
use Fuel\Foundation\Formatter\FormatterInterface;
use League\Container\ContainerInterface;

/**
 * Keeps track of active formatters and facilitates the formatting of a controller response.
 */
class ResponseFormatter
{
	/**
	 * @var FormatterInterface[]
	 */
	protected $formatterClasses = [];

	/**
	 * @var ContainerInterface
	 */
	protected $dependencyContainer;

	/**
	 * ResponseFormatter constructor.
	 *
	 * @param string[]           $formatters          List of class names or DIC instance names
	 * @param ContainerInterface $dependencyContainer
	 */
	public function __construct($formatters, $dependencyContainer)
	{
		$this->dependencyContainer = $dependencyContainer;

		foreach ($formatters as $formatter) {
			$formatterInstance = $dependencyContainer->get($formatter);

			if (! $formatterInstance instanceof FormatterInterface) {
				throw new FormatterLoad("FOU-003: Unable to load [$formatter]: Does not implement FormatterInterface");
			}

			$formatterInstance->setContainer($dependencyContainer);
			$this->formatterClasses[$formatter] = $formatterInstance;
		}
	}

	/**
	 * @return FormatterInterface[]
	 */
	public function getFormatters()
	{
		return $this->formatterClasses;
	}

	/**
	 * @param mixed $data Result returned by the controller.
	 *
	 * @return FormatterInterface
	 */
	public function getFormatter($data)
	{
		foreach ($this->formatterClasses as $formatter) {
			if ($formatter->canActivate($data)) {
				return $formatter;
			}
		}

		return null;
	}

	/**
	 * Attempts to run a registered formatter on the given data.
	 *
	 * @param mixed $data
	 */
	public function format($data)
	{
		$formatter = $this->getFormatter($data);

		if ($formatter === null) {
			throw new Formatter('FOU-004: No formatter could be found');
		}

		$formatter->format($data);
	}
}
