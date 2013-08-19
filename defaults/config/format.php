<?php
/**
 * @package    Fuel
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

/**
 * NOTICE:
 *
 * This is the global configuration for the FuelPHP framework. It contains
 * configuration which is global for all installed applications.
 */

return array(
	'csv' => array(
		'delimiter' => ',',
		'enclosure' => '"',
		'newline'   => "\n",
		'regex_newline'   => '\n',
		'escape'    => '\\',
	),
	'xml' => array(
		'basenode' => 'xml',
	),
);
