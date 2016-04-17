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

use Fuel\Config\Container as ConfigContainer;
use Fuel\Dependency\Container as DependencyContainer;
use Fuel\Foundation\Event\AppStarted;
use League\Container\ContainerInterface;
use League\Event\Emitter;

class Application
{
	/**
	 * @var ContainerInterface
	 */
	protected $dependencyContainer;

	/**
	 * @var array
	 */
	protected $config;

	public static function init(array $config) : Application
	{
		// Ensure the needed config entries exists
		$config['events'] = $config['events'] ?? [];
		$config['components'] = $config['components'] ?? [];

		return new static($config);
	}

	public function __construct(array $config, ContainerInterface $dependencyContainer = null)
	{
		$this->initDependencyContainer($config, $dependencyContainer);

		// register any events from the config
		$this->registerEvents($config['events']);

		// Load components
		$this->loadComponents($config['components']);

		// trigger app created event
		$this->dependencyContainer
			->get('fuel.application.event')
			->emit(new AppStarted($this));
	}

	public function setDependencyContainer(ContainerInterface $dependencyContainer)
	{
		$this->dependencyContainer = $dependencyContainer;
	}

	public function getDependencyContainer() : ContainerInterface
	{
		return $this->dependencyContainer;
	}

	public function run()
	{
		// TODO: trigger request started event

		// TODO: route to and call controller

		// TODO: trigger request ended event

		// TODO: trigger response started event

		// TODO: generate and send response

		// TODO: send shutdown event
	}

	/**
	 * @param array $events
	 */
	protected function registerEvents(array $events)
	{
		/** @var Emitter $eventContainer */
		$eventContainer = $this->dependencyContainer->get('fuel.application.event');

		foreach ($events as $event)
		{
			$eventContainer->addListener(
				$event['name'],
				$event['listener'],
				$event['priority'] ?? $eventContainer::P_NORMAL
			);
		}
	}

	/**
	 * @param string[] $components
	 */
	protected function loadComponents(array $components)
	{
		/** @var ComponentManagerInterface $componentManager */
		$componentManager = $this->getDependencyContainer()
			->get('fuel.application.component_manager');

		foreach ($components as $component)
		{
			$componentManager->load($component);
		}
	}

	/**
	 * @param array              $config
	 * @param ContainerInterface $dependencyContainer
	 */
	protected function initDependencyContainer(array $config, ContainerInterface $dependencyContainer = null)
	{
		$this->setDependencyContainer($dependencyContainer ?? new DependencyContainer());
		$this->dependencyContainer->add('fuel.application', $this);
		$this->dependencyContainer->add('fuel.application.config', $config);
		$this->dependencyContainer->addServiceProvider(new ApplicationServicesProvider());
	}

}
