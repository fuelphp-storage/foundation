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

namespace Fuel\Foundation\Event;

use Fuel\Foundation\Application;
use League\Event\AbstractEvent;

/**
 * Triggered when a response has been started.
 *
 * @package Fuel\Foundation\Event
 */
class ResponseStarted extends AbstractAppEvent
{
	protected $name = 'fuel.response.started';
}
