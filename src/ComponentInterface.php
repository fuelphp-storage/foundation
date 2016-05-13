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

/**
 * Common interface for components
 *
 * @package Fuel\Foundation
 */
interface ComponentInterface
{
	/**
	 * Gets the directory where the component's configs can be found.
	 * This uses reflection so if you have a large number of components it would be advisable to extend this and use
	 * `__DIR__` to generate a return value.
	 *
	 * @return string
	 */
	public function getPath() : string ;
}
