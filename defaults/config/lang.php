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

	/**
	 * Current language for this application
	 */
	'current' => 'en',

	/**
	 * Fallback language for this application, will be used if no language file exists
	 * for the current language set here or by the application
	 */
	'fallback' => 'en',

	/**
	 * What to return if the requested Language key does not exist? You can use {key} in
	 * the string which will be replaced by the requested (and missing) key value
	 */
	'default' => '<span style="background-color:red;color:white;font-weight:bold;">{key}</span>',
);
