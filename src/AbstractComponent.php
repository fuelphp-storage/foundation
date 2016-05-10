<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2016 Fuel Development Team
 * @link       http://fuelphp.com
 */

declare(strict_types=1);

namespace Fuel\Foundation;

use ReflectionClass;

abstract class AbstractComponent implements ComponentInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function getConfigPath() : string
	{
		$reflection = new ReflectionClass(static::class);
		return dirname($reflection->getFileName()) . '/..';
	}
}
