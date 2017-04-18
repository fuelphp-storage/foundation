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

/**
 * Interface ComponentManagerInterface
 *
 * @package Fuel\Foundation
 * @author Fuel Development Team
 */
interface ComponentManagerInterface
{
	/**
	 * Gets a component, loading it if needed.
	 *
	 * @param string $name
	 *
	 * @return ComponentInterface
	 */
	public function get(string $name) : ComponentInterface ;

	/**
	 * Loads the given component.
	 *
	 * @param string $name
	 *
	 * @return ComponentInterface
	 */
	public function load(string $name) : ComponentInterface ;

	/**
	 * Loads a component by name.
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function loaded(string $name) : bool ;

	/**
	 * Unloads a component.
	 *
	 * @param string $name
	 *
	 * @return ComponentManagerInterface
	 */
	public function unload(string $name) : ComponentManagerInterface ;

}
