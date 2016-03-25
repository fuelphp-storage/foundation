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

use Fuel\Foundation\Exception\ComponentLoad;

/**
 * Keeps track of fuel's components
 *
 * @package Fuel\Foundation
 */
class ComponentManager implements ComponentManagerInterface
{
	protected static $componentClassName  = 'FuelComponent';

	/**
	 * @var ComponentInterface[]
	 */
	protected $loadedComponents = [];

	/**
	 * Contains a list of component paths for components that have already been requested, indexed by component name.
	 *
	 * @var string[]
	 */
	protected $availableComponents = [];

	/**
	 * Gets a component, loading it if needed.
	 *
	 * @param string $name
	 *
	 * @return ComponentInterface
	 */
	public function get(string $name) : ComponentInterface
	{
		if ($this->loaded($name))
		{
			return $this->loadedComponents[$name];
		}

		return $this->load($name);
	}

	/**
	 * Loads the given component.
	 *
	 * TODO: Also load dependant components
	 *
	 * @param string $name
	 *
	 * @return ComponentInterface
	 */
	public function load(string $name) : ComponentInterface
	{
		$fullName = $name . '\\' . static::$componentClassName;

		// Check if component class exists
		if ( ! class_exists($fullName))
		{
			throw new ComponentLoad("FOU-001: Unable to load [$fullName]: Class not found");
		}

		// Check if it implements the correct interface
		if ( ! in_array('Fuel\Foundation\ComponentInterface', class_implements($fullName)))
		{
			throw new ComponentLoad("FOU-002: Unable to load [$fullName]: Does not implement ComponentInterface");
		}

		// Load the component
		$component = new $fullName();

		$this->loadedComponents[$name] = $component;

		return $component;
	}

	/**
	 * Loads a component by name.
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function loaded(string $name) : bool
	{
		return isset($this->loadedComponents[$name]);
	}

	/**
	 * Unloads a component.
	 *
	 * @param string $name
	 *
	 * @return ComponentManagerInterface
	 */
	public function unload(string $name) : ComponentManagerInterface
	{
		if ($this->loaded($name))
		{
			unset($this->loadedComponents[$name]);
		}

		return $this;
	}

}
