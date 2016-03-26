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

use Fuel\Dependency\Container;
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
		return new static($config);
	}

	public function __construct(array $config, ContainerInterface $dependencyContainer = null)
	{
		// Ensure the DI entry exists
		$config['di'] = $config['di'] ?? [];

		$this->setDependencyContainer($dependencyContainer ?? new Container($config));
		$this->dependencyContainer->add('fuel.application', $this);
		$this->dependencyContainer->addServiceProvider(new ApplicationServicesProvider());

		// register any events from the config
		$config['events'] = $config['events'] ?? [];
		$this->registerEvents($config['events']);

		// Load components
		$config['components'] = $config['components'] ?? [];
		$this->loadComponents($config['components']);

		$this->config = $config;

		// trigger app created event
		$this->dependencyContainer
			->get('fuel.application.event')
			->emit(new AppStarted());
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

}
