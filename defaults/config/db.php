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
 *
 * The Fuel Database package uses PDO for all databases !
 */

return array(

	/*
	 * If you don't specify a DB configuration name when you create a connection
	 * the configuration to be used will be determined by the 'active' value
	 */
	'active' => 'default',

	/**
	 * Base config
	 */
	'default' => array(
		'profiling'         => false,              // enable or disable database profiling

		'autoConnect'       => true,               // automatically connect when the DB class is loaded

		'username'          => 'webdev',                 // user used for the database connection
		'password'          => 'webdev',                 // password of this user

		'database'          => 'DEV_Propellant',               // name of the database to connect to

		'host'              => 'localhost',        // hostname or IP of your database server
		'port'              => null,               // any non-standard TCP port this server runs on
		'socket'            => null,               // or the unix_socket if it uses that
		'persistent'        => true,               // if true, use persistent database connections

		'charset'           => 'utf8',             // characterset used by this database
		'collate'			=> null,               // optional collating sequence to be used

		'asObject'          => true,               // if true, return objects, if false return assoc arrays
		'lateProperties'    => false,              // if true, objects will be populated AFTER the constructor has been called
		'resultCollection'  => null,               // NOT-USED-YET

		'attributes'        => array(),            // custom or additional PDO attributes to pass to a new PDO object

		'insertIdField'     => 'id',               // for PostgreSQL: the default 'id' column, to emulate getLastInsertId()
	),

);
