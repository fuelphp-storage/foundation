<?php
/**
 * @package    Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
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
	 * Default cookie expiration
	 */
	'expiration'  => 0,

	/**
	 * Default cookie path
	 */
	'path'        => '/',

	/**
	 * Default cookie domain
	 */
	'domain'      => null,

	/**
	 * If true, the cookie is only send over a secure HTTPS connection
	 */
	'secure'      => false,

	/**
	 * If true, the cookie is client-side not accessable via javascript
	 */
	'http_only'   => false,
);
