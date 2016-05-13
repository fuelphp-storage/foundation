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
 * Triggered when a request has been finished.
 *
 * @package Fuel\Foundation\Event
 */
class RequestFinished extends AbstractAppEvent
{
	protected $name = 'fuel.request.finished';
}
