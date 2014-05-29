<?php
/**
 * @package    Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

/**
 * NOTICE:
 *
 * If you need to make modifications to the default configuration, copy
 * this file to your applications config folder, and make them in there.
 *
 * This will allow you to upgrade fuel without losing your custom config.
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
