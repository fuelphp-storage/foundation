<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Foundation\Event;

use Fuel\Foundation\Application;
use League\Event\AbstractEvent;

/**
 * Shutdown event
 *
 * @package Fuel\Foundation
 *
 * @since 2.0.0
 */
class Shutdown extends AbstractEvent
{
	/**
	 * Current context
	 *
	 * @var Application
	 */
	private $app;

	/**
	 * @param Application $app
	 */
	public function __construct(Application $app)
	{
		$this->app = $app;
	}

	/**
	 * Returns the context
	 *
	 * @return Application
	 */
	public function getApp()
	{
		return $this->app;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return 'shutdown';
	}
}
