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

	// fileinfo() magic filename
	'magic_file' => null,

	// default file and directory permissions
	'chmod' => array(

		/**
		 * Permissions for newly created files
		 */
		'files'  => 0666,

		/**
		 * Permissions for newly created directories
		 */
		'folders'  => 0777,
	),

);
