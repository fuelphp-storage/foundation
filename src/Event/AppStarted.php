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

use League\Event\AbstractEvent;

/**
 * Triggered when the application has finished setting up.
 *
 * @package Fuel\Foundation\Event
 */
class AppStarted extends AbstractEvent
{
	public function getName()
	{
		return 'fuel.application.started';
	}
}
