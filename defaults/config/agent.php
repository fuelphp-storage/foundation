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
	 * Method used to fetch user agent information.
	 *
	 * Currently supported are: "browscap"
	 */
	'method' => 'browscap',

	/**
	 * Whether of not all property names should be in lowercase
	 *
	 * set to false to get the properties in original case (like JavaApplets).
	 */
	'lowercase' => true,

	/**
	 * Path in which to store cached browser information
	 *
	 * Must be writable by the application.
	 */
	'cache_dir' => realpath(__DIR__.DS.'..'.DS.'cache'),

	/**
	 * browscap parsing configuration.
	 *
	 */
	'browscap' => array(

		/**
		 * Wether or not to auto-refresh the cache if the interval has expired
		 */
		'autoUpdate' => true,

		/**
		 * Update interval, number of seconds after which a cached browscap data expires.
		 *
		 *	Default: 604800 (every 7 days)
		 *
		 * Note that to prevent abuse of the site publishing the browsecap files,
		 * you can not set the expiry time lower than 7200 (2 hours)
		 */
		'updateInterval' => 604800,

		/**
		 * If you use a manual process to download the browscap.ini file, use this key
		 * to define the location of the downloaded file. If defined, it will have
		 * precendence over any download method.
		 */
		'localFile' => null,

		/**
		 * Location from where the updated browscap file can be downloaded.
		 */
		'remoteIniUrl' => 'http://browscap.org/stream?q=Lite_PHP_BrowsCapINI',     // only major browsers and search engines
		//'remoteIniUrl' => 'http://browscap.org/stream?q=Full_PHP_BrowsCapINI',   // complete file, approx. 3 times the lite version

		/**
		 * The location to use to check out if a new version of the browscap.ini file is available
		 */
		'remoteVerUrl' => 'http://browscap.org/version',

		/**
		 * Filename used to store the cached browscap data.
		 */
		'cacheFilename' => 'browscap.cache',

		/**
		 * Filename used to store the downloaded browscap ini file.
		 */
		'iniFilename' => 'browscap.ini',
	),

);


