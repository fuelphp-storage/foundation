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

	/**
	 * Global configuration, will be used as the base for all Auth instances
	 */
	'global' => array(

		// auth driver configuration
		'drivers' => array(
		),

		// what to use as a backend for user link storage
		'storage' => null,

		// what to use as a backend for persistence
		'persistence' => null,

		// whether or not you want to use all user drivers simultaneously
		'use_all_drivers' => false,

		// whether or not you want reduce the result if only a single driver is used
		'always_return_arrays' => true,

		// salt, used to hash the user passwords
		'salt' => 'put_your_salt_here',

		// number of iterations for the PBKDF2 hashing algorithm
		'iterations' => 10000,
	),

);
