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
