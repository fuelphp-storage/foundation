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

use Fuel\Foundation\Response\ResponseInterface;
use League\Container\ContainerInterface;

/**
 * Defines a common interface for taking a controller result and formatting that for return to the client.
 */
interface FormatterInterface
{
	/**
	 * Sets the dependency container that the formatter will use.
	 *
	 * @param ContainerInterface $container
	 */
	public function setContainer(ContainerInterface $container);

	/**
	 * Gets the current dependency container.
	 *
	 * @return ContainerInterface
	 */
	public function getContainer(): ContainerInterface;

	/**
	 * Should return true if the formatter is able to handle the current data.
	 *
	 * @param mixed $data Result returned from the controller's action. This allows the formatter to determine if the
	 *                    data is in a format that it can support or not.
	 *
	 * @return bool
	 */
	public function canActivate($data): bool;

	/**
	 * Should produce a ResponseInterface object that contains the formatted data and any necessary headers.
	 *
	 * @param mixed $data Result returned from the controller's action.
	 *
	 * @return ResponseInterface
	 */
	public function format($data): ResponseInterface;
}
