<?php
/**
 * Part of the FuelPHP framework.
 *
 * @package    FuelPHP\Foundation
 * @version    2.0
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 */

// kickstart the framework
\FuelPHP\Foundation\FuelPHP::kickstart();

// alias the FuelPHP class to global for easier access
\FuelPHP\Foundation\FuelPHP::alias('FuelPHP', 'FuelPHP\Foundation\FuelPHP');

// run the library loader to create the other class aliases
require __DIR__.'/loader.php';

// setup the FuelPHP environment
\FuelPHP::register('Environment', 'Environment', function ($entry) {
	$entry->preferSingleton();
});
