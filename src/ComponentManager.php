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

use Fuel\Config\ContainerInterface;
use Fuel\Foundation\Exception\ComponentLoad;

/**
 * Keeps track of fuel's components
 *
 * @package Fuel\Foundation
 */
class ComponentManager implements ComponentManagerInterface
{
	/**
	 * Name of the component class to look for.
	 *
	 * @var string
	 */
	protected static $componentClassName  = 'FuelComponent';

	/**
	 * @var ComponentInterface[]
	 */
	protected $loadedComponents = [];

	/**
	 * @var ContainerInterface
	 */
	protected $configContainer;

	public function __construct(ContainerInterface $configContainer)
	{
		$this->configContainer = $configContainer;
	}

	/**
	 * {@inheritdoc}
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
	 * {@inheritdoc}
	 */
	public function load(string $name) : ComponentInterface
	{
		$fullName = $name . '\\' . static::$componentClassName;

		// Check if component class exists
		if ( ! class_exists($fullName))
		{
			throw new ComponentLoad("FOU-001: Unable to load [$fullName]: Class not found");
		}

		// Load the component
		$component = new $fullName();

		// Check if it implements the correct interface
		if ( ! $component instanceof ComponentInterface)
		{
			throw new ComponentLoad("FOU-002: Unable to load [$fullName]: Does not implement ComponentInterface");
		}

		$this->loadedComponents[$name] = $component;

		$this->addConfigPath($component);

		return $component;
	}

	protected function addConfigPath(ComponentInterface $component)
	{
		$this->configContainer->addPath($component->getConfigPath());
	}

	/**
	 * {@inheritdoc}
	 */
	public function loaded(string $name) : bool
	{
		return isset($this->loadedComponents[$name]);
	}

	/**
	 * {@inheritdoc}
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
