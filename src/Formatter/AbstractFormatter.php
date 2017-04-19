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

namespace Fuel\Foundation\Formatter;

use League\Container\ContainerInterface;

abstract class AbstractFormatter implements FormatterInterface
{
	/**
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * Sets the dependency container that the formatter will use.
	 *
	 * @param ContainerInterface $container
	 */
	public function setContainer(ContainerInterface $container)
	{
		$this->container = $container;
	}

	/**
	 * Gets the current dependency container.
	 *
	 * @return ContainerInterface
	 */
	public function getContainer(): ContainerInterface
	{
		return $this->container;
	}
}
