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

namespace Fuel\Foundation\Event;

use Fuel\Foundation\Application;
use League\Event\AbstractEvent;

/**
 * Triggered when the application has finished setting up.
 *
 * @package Fuel\Foundation\Event
 */
class AppStarted extends AbstractEvent
{
	/**
	 * @var Application
	 */
	protected $application;

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
		return 'fuel.application.started';
	}
}
