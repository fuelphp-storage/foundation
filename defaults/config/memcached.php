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

	/*
	 * If you don't specify a memcached configuration name when you create a
	 * connection the configuration to be used will be determined by the
	 * 'active' value
	 */
	'active' => 'default',

	/**
	 * Default memached config
	 */
	'default' => array(

		// any persistent_id you want to use to re-use an existing connection pool
		'persistent_id' => null,

		// array of servers and portnumbers that run the memcached service
		'servers' => array(
			array('host' => '127.0.0.1', 'port' => 11211, 'weight' => 100)
		),

		// array of custom memcached configuration options
		'options' => array(
			\Memcached::OPT_HASH => \Memcached::HASH_DEFAULT,
			\Memcached::OPT_BUFFER_WRITES => true,

		),
	),

);
