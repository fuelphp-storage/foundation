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

// alias the class to global for easier access
\FuelPHP\Foundation\FuelPHP::aliasNamespace('FuelPHP\Foundation', '');

// setup the FuelPHP environment
\FuelPHP::register('Environment', 'Environment', function ($entry) {
	$entry->preferSingleton();
});
