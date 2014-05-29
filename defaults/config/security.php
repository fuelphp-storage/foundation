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
	 * CSRF protection
	 */
	'csrf' => array(

		/**
		 * If true, every page containing a form that POSTs data will be
		 * automatically protected by the framework against CSRF attacks
		 */
		'enabled' => true,

		/**
		 * The Security Csrf driver used to provide the protection mechanism.
		 * The framework provides the following drivers:
		 * noop - for testing purposes, uses a fixed test-token, and validates all responses
		 * session - uses a single token per user-session, with optional expiration/rotation
		 * page - protects individual pages with unique tokens, requires cookie and javascript support
		 * form - protects individual forms with unique tokens, requires cookie and javascript support
		 */
		'driver' => 'session',

		/**
		 * A salt to make sure the generated security tokens are not predictable
		 */
		'salt' => 'put your salt value here to make the token more secure',

		/**
		 * list of HTTP methods that will require CSRF protection
		 */
		'http_methods' => array('post', 'put', 'delete'),

		/**
		 * Name of the form field that holds the CSRF token.
		 */
		'token_key'        => 'fuel_csrf_token',

		/**
		 * Expiry of the token in seconds. If zero, the token remains the same
		 * for the entire user session.
		 */
		'expiration'       => 0,
	),

	/**
	 * Allow the Input class to use X headers when present
	 *
	 * Examples of these are HTTP_X_FORWARDED_FOR and HTTP_X_FORWARDED_PROTO, which
	 * can be faked which could have security implications
	 */
	'allow_x_headers'       => false,

	/**
	 * This input filter can be any normal PHP function as well as 'xss_clean'
	 *
	 * WARNING: Using xss_clean will cause a performance hit.
	 * How much is dependant on how much input data there is.
	 */
	'uri_filter'       => array(
		'HtmlEntities'
	),

	/**
	 * This input filter can be any normal PHP function as well as 'xss_clean'
	 *
	 * WARNING: Using xss_clean will cause a performance hit.
	 * How much is dependant on how much input data there is.
	 */
	'input_filter'  => array(),

	/**
	 * This output filter can be any normal PHP function as well as 'xss_clean'
	 *
	 * WARNING: Using xss_clean will cause a performance hit.
	 * How much is dependant on how much input data there is.
	 */
	'output_filter'  => array(
		'HtmlEntities'
	),

	/**
	 * Encoding mechanism to use on htmlentities()
	 */
	'htmlentities_flags' => ENT_QUOTES,

	/**
	 * Encoding to be used when converting
	 */
	'htmlentities_encoding' => 'UTF-8',

	/**
	 * Wether to encode HTML entities as well
	 */
	'htmlentities_double_encode' => false,

	/**
	 * Whether to automatically filter view data
	 */
	'auto_filter_output'  => true,

	/**
	 * With output encoding switched on all objects passed will be converted to strings or
	 * throw exceptions unless they are instances of the classes in this array.
	 */
	'whitelisted_classes' => array(),
);
