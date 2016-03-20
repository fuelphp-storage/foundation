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
use League\Container\ContainerInterface;

class Application
{
	/**
	 * @var ContainerInterface
	 */
	protected $dependencyContainer;

	public static function init(array $config) : Application
	{
		return new static($config);
	}

	public function __construct(array $config, ContainerInterface $dependencyContainer = null)
	{
		$this->setDependencyContainer($dependencyContainer ?? new Container($config));
		$this->dependencyContainer->add('fuel.application', $this);
		$this->dependencyContainer->addServiceProvider(new ApplicationServicesProvider());
	}

	public function setDependencyContainer(ContainerInterface $dependencyContainer)
	{
		$this->dependencyContainer = $dependencyContainer;
	}

	public function getDependencyContainer() : ContainerInterface
	{
		return $this->dependencyContainer;
	}

}
