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

/**
 * Triggered when the application has finished all request processing and the app is shutting down.
 *
 * @package Fuel\Foundation\Event
 */
class AppShutdown extends AbstractAppEvent
{
	protected $name = 'fuel.application.shutdown';
}
