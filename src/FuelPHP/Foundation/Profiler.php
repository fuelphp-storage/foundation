<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    Fuel\Core
 * @version    2.0.0
 * @license    MIT License
 * @copyright  2010 - 2012 Fuel Development Team
 */

namespace FuelPHP\Foundation;

/**
 * Profiler class - TEMPORARY SOLUTION !
 *
 * The Profiler class collects information about your application being run from various sources.
 *
 * @package  Fuel\Core
 *
 * @since  1.0.0
 */
class Profiler
{
	/**
	 * @var  Environment
	 *
	 * @since  2.0.0
	 */
	protected $env;

	/**
	 * Constructor
	 *
	 * @since  2.0.0
	 */
	public function __construct()
	{
		// set the environment variable necessary for the package loader object
		$this->env = \FuelPHP::resolve('Environment');
	}

	/**
	 * Fetch the time that has elapsed since Environment init
	 *
	 * @return  float
	 *
	 * @since  2.0.0
	 */
	public function getTimeElapsed()
	{
		return microtime(true) - $this->env->getVar('initTime');
	}

	/**
	 * Fetch the mem usage change since Environment init
	 *
	 * @param   bool  $peak  whether to report the peak usage instead of the current
	 * @return  float
	 *
	 * @since  2.0.0
	 */
	public function getMemUsage($peak = false)
	{
		$usage = $peak ? memory_get_peak_usage() : memory_get_usage();
		return $usage - $this->env->getVar('initMem');
	}
}
