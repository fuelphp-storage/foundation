<?php
/**
 * @package    demo-application
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

/**
 * NOTICE:
 *
 * This is the application configuration for this FuelPHP application.
 * It contains configuration which is for this application only.
 */

return array(

	// formatPhone()
	'phone' => '(000) 000-0000',

	// smartFormatPhone()
	'smartPhone' => array(
		7  => '000-0000',
		10 => '(000) 000-0000',
		11 => '0 (000) 000-0000',
	),

	// formatExp()
	'exp' => '00-00',

	// maskCreditCard()
	'creditCard' => '**** **** **** 0000',

);
