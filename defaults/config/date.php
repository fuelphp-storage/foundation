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
	 * Do NOT change this value, unless the timezone of your server is NOT the same timezone
	 * you want the application to run in. For example, if the server time is in UTC, but you
	 * want the application to run in CEST, set the offset to 3600 (because it is GMT+1).
	 */
	'gmtOffset' => 0,

	/**
	 * A couple of named patterns that are often used. Use the formats defined
	 * for strftime: http://php.net/manual/en/function.strftime.php
	 */
	'patterns' => array(
		'local'		 => '%c',

		'mysql'		 => '%Y-%m-%d %H:%M:%S',
		'mysql_date' => '%Y-%m-%d',

		'us'		 => '%m/%d/%Y',
		'us_short'	 => '%m/%d',
		'us_named'	 => '%B %d %Y',
		'us_full'	 => '%I:%M %p, %B %d %Y',
		'eu'		 => '%d/%m/%Y',
		'eu_short'	 => '%d/%m',
		'eu_named'	 => '%d %B %Y',
		'eu_full'	 => '%H:%M, %d %B %Y',

		'24h'		 => '%H:%M',
		'12h'		 => '%I:%M %p'
	)
);
