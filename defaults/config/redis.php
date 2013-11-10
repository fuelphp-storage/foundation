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
 *
 * NOTE: FuelPHP supports the following Redis classes:
 * - Redis, the PHP Redis PECL extension (or a PHP emulation of it)
 * - Predis, https://github.com/nrk/predis, or 'predis/predis' via composer
 * - Redisent, https://github.com/jdp/redisent, or 'redisent/redisent' via composer
 *   (NOTE in you will need to remove the CRLF constant definition in the Redis class)
 *
 * If none is defined, FuelPHP will default to the Redis PECL extension
 */

return array(

	/*
	 * If you don't specify a redis configuration name when you create a
	 * connection the configuration to be used will be determined by the
	 * 'active' value
	 */
	'active' => 'default',

	/**
	 * Default redis config
	 */
	'default' => array(

		// name of the class used to connect to the server
		'class'     => 'Redis',

		// array of servers and portnumbers that run the redis service
		// note: not all drivers support more than one server definition!
		'servers' => array(
			array('host' => '127.0.0.1', 'port' => 6379, 'timeout' => null, 'auth' => null, 'database' => 0)
		),

		// array of custom redis configuration options
		'options'   => array(
		),

	),

);
