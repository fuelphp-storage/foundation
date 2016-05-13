<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2016 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation\Event;

use Fuel\Foundation\Application;
use League\Event\AbstractEvent;

abstract class AbstractAppEvent extends AbstractEvent
{
	/**
	 * @var Application
	 */
	protected $application;

	/**
	 * @var string
	 */
	protected $name;

	public function __construct(Application $application)
	{
		$this->application = $application;
	}

	public function getApplication() : Application
	{
		return $this->application;
	}

	public function getName() : string
	{
		return $this->name;
	}
}
